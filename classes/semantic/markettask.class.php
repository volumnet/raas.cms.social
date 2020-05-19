<?php
namespace RAAS\CMS\Social;

use SOME\SOME;
use SOME\Namespaces;
use RAAS\Exception;
use RAAS\CMS\Material;
use RAAS\CMS\Snippet;
use Mustache_Engine;
use SOME\Text;
use RAAS\CMS\Page;

class MarketTask extends Task
{
    protected static $tablename2 = 'cms_social_market_tasks';
    protected static $uploadImageFunction = 'uploadMarketImage';

    protected static $references = array(
        'materialType' => array('classname' => 'RAAS\\CMS\\Material_Type', 'FK' => 'material_type_id', 'cascade' => true),
        'profile' => array('classname' => 'RAAS\\CMS\\Social\\Profile', 'FK' => 'profile_id', 'cascade' => true),
        'group' => array('classname' => 'RAAS\\CMS\\Social\\Group', 'FK' => 'group_id', 'cascade' => true),
        'interface' => array('classname' => 'RAAS\\CMS\\Snippet', 'FK' => 'interface_id'),

        'rootPage' => array('classname' => 'RAAS\\CMS\\Page', 'FK' => 'root_page_id'),
        'nameField' => array('classname' => 'RAAS\\CMS\\Material_Field', 'FK' => 'name_field_id'),
        'markerField' => array('classname' => 'RAAS\\CMS\\Material_Field', 'FK' => 'marker_field_id'),
        'imageField' => array('classname' => 'RAAS\\CMS\\Material_Field', 'FK' => 'image_field_id'),
        'priceField' => array('classname' => 'RAAS\\CMS\\Material_Field', 'FK' => 'price_field_id'),
        'albumNameField' => array('classname' => 'RAAS\\CMS\\Page_Field', 'FK' => 'album_name_field_id'),
        'albumImageField' => array('classname' => 'RAAS\\CMS\\Page_Field', 'FK' => 'album_image_field_id'),
    );

    public function __construct($import_data = null)
    {
        parent::__construct($import_data);
        $SQL_query = "SELECT * FROM " . static::$dbprefix . static::$tablename2 . " WHERE id = " . (int)$this->id;
        if ($SQL_result = self::$SQL->getline($SQL_query)) {
            foreach ($SQL_result as $key => $val) {
                if (($key != 'id') && !isset($this->$key)) {
                    $this->$key = $val;
                }
            }
        }
    }


    protected function getAddData()
    {
        return array(
            'id' => (int)$this->id,
            'root_page_id' => (int)$this->root_page_id,
            'category_id' => (int)$this->category_id,
            'name_field_id' => (int)$this->name_field_id,
            'marker_field_id' => (int)$this->marker_field_id,
            'price_field_id' => (int)$this->price_field_id,
            'album_name_field_id' => (int)$this->album_name_field_id,
            'album_image_field_id' => (int)$this->album_image_field_id,
        );
    }


    public function _name()
    {
        $classname = Namespaces::getClass($this->profile->networkClass);
        $name = $this->materialType->name . ' → '
              . $classname . ': '
              . ($this->group->id ? $this->group->name : $this->profile->name)
              . ' (' . View_Web::i()->_('MARKET') . ')';
        return $name;
    }


    public function publishAlbums(array $items)
    {
        foreach ($items as $item) {
            $this->publishAlbum($item);
        }
    }


    public function publishAlbum(Page $page, MarketAlbum $album = null)
    {
        try {
            if (!$album) {
                $albums = MarketAlbum::getSet(array(
                    'where' => array("task_id = " . (int)$this->id, "page_id = " . (int)$page->id),
                    'orderBy' => "id DESC"
                ));
                if ($albums) {
                    $album = array_shift($albums);
                }
            }
            if (!in_array($this->root_page_id, $page->parents_ids)) {
                throw new Exception('ERROR_PUBLISH');
            }
            if ($this->interface->id) {
                $album = $this->interface->process(array('task' => $this, 'page' => $page, 'album' => $album));
            } else {
                $album = $album ?: new MarketAlbum(array(
                    'task_id' => (int)$this->id,
                    'page_id' => (int)$page->id,
                    'group_id' => (int)$this->group_id,
                    'profile_id' => (int)$this->profile_id,
                ));
                if ($this->albumImageField->id) {
                    $attachment = $page->fields[$this->albumImageField->urn]->doRich();
                    if (!$album->id || ($attachment->id != $album->attachment_id)) {
                        if ($uploadData = $this->profile->network->uploadMarketImage($attachment, $this)) {
                            $album->attachment_id = $attachment->id;
                            $album->image_iid = trim($uploadData['id']);
                            $album->image_url = trim($uploadData['url']);
                        }
                    }
                }
                $name = Text::cuttext(
                    $this->albumNameField->id ? $album->fields[$this->albumNameField->urn]->doRich() : $album->name,
                    128,
                    '...'
                );
                if ($postData = $this->profile->network->uploadMarketAlbum($this, $name, $album)) {
                    $album->iid = trim($postData['id']);
                    $album->url = trim($postData['url']);
                    $album->post_date = date('Y-n-d H:i:s');
                    $album->name = trim($postData['name']);
                    $album->commit();
                }
                return $album;
            }
        } catch (Exception $e) {
        }
    }


    protected function getDocumentUploads(Material $item, MarketPost $post = null)
    {
        return array();
    }


    public function getPost(Material $item, array $imagesUploads, array $documentUploads, MarketPost $post = null)
    {
        $post = parent::getPost($item, $imagesUploads, array(), $post);
        if ($post && $this->albums) {
            $post->addToAlbums($item, $post);
        }
    }
}
