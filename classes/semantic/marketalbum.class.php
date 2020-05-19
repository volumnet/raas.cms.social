<?php
namespace RAAS\CMS\Social;

use SOME\SOME;
use RAAS\Exception;

class MarketAlbum extends Post
{
    protected static $tablename = 'cms_social_market_albums';
    protected static $defaultOrderBy = "post_date";
    protected static $references = array(
        'task' => array('classname' => 'RAAS\\CMS\\Social\\MarketTask', 'FK' => 'market_task_id'),
        'page' => array('classname' => 'RAAS\\CMS\\Page', 'FK' => 'page_id'),
        'image' => array('classname' => 'RAAS\\Attachment', 'FK' => 'attachment_id'),
        'group' => array('classname' => 'RAAS\\CMS\\Social\\Group', 'FK' => 'group_id'),
        'profile' => array('classname' => 'RAAS\\CMS\\Social\\Profile', 'FK' => 'profile_id'),
    );

    public function update()
    {
        return $this->task->publishAlbum($this->page, $this);
    }


    public static function delete(self $item)
    {
        try {
            $result = $item->task->profile->network->deleteMarketAlbum($item);
        } catch (Exception $e) {
        }
        if ($result) {
            parent::delete($item);
            return true;
        }
        return false;
    }
}
