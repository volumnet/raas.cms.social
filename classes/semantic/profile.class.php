<?php
namespace RAAS\CMS\Social;

use SOME\SOME;

class Profile extends SOME
{
    protected static $tablename = 'cms_social_profiles';
    protected static $defaultOrderBy = "name";
    protected static $children = array(
        'tasks' => array('classname' => 'RAAS\\CMS\\Social\\Task', 'FK' => 'profile_id'),
        'uploads' => array('classname' => 'RAAS\\CMS\\Social\\Upload', 'FK' => 'profile_id'),
        'posts' => array('classname' => 'RAAS\\CMS\\Social\\Post', 'FK' => 'profile_id'),
    );


    protected static $cognizableVars = array('networkClass', 'network');

    public function _network()
    {
        $classname = Network::getNetwork($this->url);
        if ($classname) {
            $network = new $classname($this);
            return $network;
        }
        return null;
    }


    public function _networkClass()
    {
        return Network::getNetwork($this->url);
    }
}
