<?php
namespace RAAS\CMS\Social;

use SOME\SOME;

class Image extends SOME
{
    protected static $tablename = 'cms_social_tasks_images';
    protected static $aiPriority = true;
    protected static $defaultOrderBy = "priority";
    protected static $references = array(
        'parent' => array('classname' => 'RAAS\\CMS\\Social\\Task', 'FK' => 'pid', /*'cascade' => true*/),
        'field' => array('classname' => 'RAAS\\CMS\\Material_Field', 'FK' => 'fid', 'cascade' => true),
    );
}
