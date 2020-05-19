<?php
namespace RAAS\CMS\Social;

use SOME\Namespaces;

class Controller_Ajax extends Abstract_Controller
{
    protected static $instance;

    protected function execute()
    {
        switch ($this->action) {
            case 'groups':
            case 'categories':
                $this->{$this->action}();
                break;
        }
    }


    protected function groups()
    {
        $profile = new Profile((int)$this->id);
        $groups = Group::getSet();
        $groups = array_filter($groups, function ($x) use ($profile) {
            return $x->networkClass == $profile->networkClass;
        });
        $OUT['Set'] = array_merge(
            array(array('val' => '', 'text' => '--')),
            array_values(
                array_map(
                    function ($x) {
                        return array('val' => $x->id, 'text' => $x->name);
                    },
                    $groups
                )
            )
        );
        $this->view->groups($OUT);
    }


    protected function categories()
    {
        $profile = new Profile((int)$this->id);
        if (!($profile->network instanceof Marketable)) {
            return array();
        }
        $temp = $profile->network->getMarketCategories();
        $cats = array();
        switch (Namespaces::getClass($profile->networkClass)) {
            case 'Vk':
                foreach ($temp as $row) {
                    $cats[] = array('val' => '', 'text' => '-- ' . $row['name'] . ' --', 'disabled' => true, 'style' => 'font-weight: bold;');
                    foreach ($row['children'] as $row2) {
                        $cats[] = array('val' => $row2['id'], 'text' => str_repeat('&nbsp;', 3) . $row2['name']);
                    }
                }
                break;
            case 'Facebook':
                break;
        }
        $OUT = array('Set' => $cats);
        $this->view->categories($OUT);
    }
}
