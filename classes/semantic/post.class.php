<?php
namespace RAAS\CMS\Social;

use SOME\SOME;
use RAAS\Exception;

class Post extends SOME
{
    protected static $tablename = 'cms_social_posts';
    protected static $defaultOrderBy = "post_date";

    protected static $deletePostFunction = 'deletePost';

    protected static $references = array(
        'task' => array('classname' => 'RAAS\\CMS\\Social\\Task', 'FK' => 'task_id'),
        'material' => array('classname' => 'RAAS\\CMS\\Material', 'FK' => 'material_id'),
        'group' => array('classname' => 'RAAS\\CMS\\Social\\Group', 'FK' => 'group_id'),
        'profile' => array('classname' => 'RAAS\\CMS\\Social\\Profile', 'FK' => 'profile_id'),
    );
    protected static $children = array(
        'uploads' => array('classname' => 'RAAS\\CMS\\Social\\Upload', 'FK' => 'post_id'),
    );

    public static function spawn($import_data)
    {
        if (is_array($import_data)) {
            $task = Task::spawn($import_data['task_id']);
        } else {
            $SQL_query = "SELECT task_id FROM " . self::_tablename() . " WHERE id = ?";
            $taskId = self::$SQL->getvalue(array($SQL_query, array($import_data)));
            $task = Task::spawn($taskId);
        }
        if ($task->is_market) {
            return new MarketTask($import_data);
        }
        return new static($import_data);
    }


    public function update()
    {
        return $this->task->publishItem($this->material, $this);
    }


    public static function delete(SOME $item)
    {
        foreach ($item->uploads as $upload) {
            Upload::delete($upload);
        }
        try {
            $result = $item->task->profile->network->{static::$deletePostFunction}($item);
        } catch (Exception $e) {
        }
        if ($result) {
            parent::delete($item);
            return true;
        }
        return false;
    }
}
