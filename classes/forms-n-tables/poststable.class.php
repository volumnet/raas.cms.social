<?php
namespace RAAS\CMS\Social;

use SOME\Namespaces;
use RAAS\Table;

class PostsTable extends Table
{
    public function __get($var)
    {
        switch ($var) {
            case 'view':
                return ViewSub_Main::i();
                break;
            default:
                return parent::__get($var);
                break;
        }
    }


    public function __construct(array $params = array())
    {
        $view = $this->view;
        $defaultParams = array(
            'meta' => array(
                'allContextMenu' => $view->getAllPostsContextMenu(),
                'allValue' => null,
            ),
            'data-role' => 'multitable',
            'template' => 'cms/multitable.tmp.php',
            'columns' => array(
                'image' => array(
                    'caption' => $this->view->_('IMAGE'),
                    'callback' => function ($row) use ($view) {
                        foreach ($row->uploads as $upload) {
                            if ($upload->attachment->image && $upload->url) {
                                $text = '<a href="' . $row->url . '" target="blank" class="cms-social-post__image">'
                                      . '  <img src="' . $upload->url . '" style="max-width: 48px;" alt="' . htmlspecialchars($row->name) . '" title="' . htmlspecialchars($row->name) . '" />'
                                      . '</a>';
                                return $text;
                            }
                        }
                    }
                ),
                'name' => array(
                    'caption' => $this->view->_('NAME'),
                    'callback' => function ($row) use ($view) {
                        $text = '<a href="' . $row->url . '" target="blank">' . htmlspecialchars($row->name) . '</a>';
                        return $text;
                    }
                ),
                'material_id' => array(
                    'caption' => $this->view->_('MATERIAL'),
                    'callback' => function ($row) use ($view) {
                        if ($row->material->id) {
                            $text = '<a href="?p=cms&action=edit_material&id=' . (int)$row->material->id . '" target="blank">'
                                  .    htmlspecialchars($row->material->name)
                                  . '</a>';
                            return $text;
                        }
                    }
                ),
                ' ' => array(
                    'callback' => function ($row) use ($view) {
                        return rowContextMenu($view->getPostContextMenu($row));
                    }
                )
            ),
            'emptyString' => $view->_('NO_NOTES_FOUND')
        );
        $arr = array_merge($defaultParams, $params);
        parent::__construct($arr);
    }
}
