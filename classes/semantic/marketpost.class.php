<?php
namespace RAAS\CMS\Social;

use SOME\SOME;
use RAAS\Exception;

class MarketPost extends Post
{
    protected static $references = array(
        'task' => array('classname' => 'RAAS\\CMS\\Social\\MarketTask', 'FK' => 'task_id'),
        'material' => array('classname' => 'RAAS\\CMS\\Material', 'FK' => 'material_id'),
        'group' => array('classname' => 'RAAS\\CMS\\Social\\Group', 'FK' => 'group_id'),
        'profile' => array('classname' => 'RAAS\\CMS\\Social\\Profile', 'FK' => 'profile_id'),
    );
    protected static $links = array(
        'albums' => array('tablename' => 'cms_social_market_items_albums_assoc', 'field_from' => 'item_id', 'field_to' => 'album_id', 'classname' => 'RAAS\\CMS\\Social\\Album'),
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
}
