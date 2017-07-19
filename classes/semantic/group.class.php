<?php
namespace RAAS\CMS\Social;

use SOME\SOME;

class Group extends SOME
{
    protected static $tablename = 'cms_social_groups';
    protected static $defaultOrderBy = "name";
    protected static $children = array(
        'tasks' => array('classname' => 'RAAS\\CMS\\Social\\Task', 'FK' => 'group_id'),
        'uploads' => array('classname' => 'RAAS\\CMS\\Social\\Upload', 'FK' => 'group_id'),
        'posts' => array('classname' => 'RAAS\\CMS\\Social\\Post', 'FK' => 'group_id'),
    );

    protected static $cognizableVars = array('networkClass');

    public function _networkClass()
    {
        return Network::getNetwork($this->url);
    }
}
