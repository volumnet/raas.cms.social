<?php
namespace RAAS\CMS\Social;

use SOME\SOME;

class Upload extends SOME
{
    protected static $tablename = 'cms_social_uploads';
    protected static $defaultOrderBy = "post_date";
    protected static $references = array(
        'task' => array('classname' => 'RAAS\\CMS\\Social\\Task', 'FK' => 'task_id'),
        'attachment' => array('classname' => 'RAAS\\Attachment', 'FK' => 'attachment_id'),
        'post' => array('classname' => 'RAAS\\CMS\\Social\\Post', 'FK' => 'post_id', 'cascade' => true),
        'material' => array('classname' => 'RAAS\\CMS\\Material', 'FK' => 'material_id'),
        'group' => array('classname' => 'RAAS\\CMS\\Social\\Group', 'FK' => 'group_id'),
        'profile' => array('classname' => 'RAAS\\CMS\\Social\\Profile', 'FK' => 'profile_id'),
    );
}
