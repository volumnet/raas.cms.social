<?php
namespace RAAS\CMS\Social;

class Controller_Ajax extends Abstract_Controller
{
    protected static $instance;

    protected function execute()
    {
        switch ($this->action) {
            case 'groups':
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
        $this->view->show_page($OUT);
    }
}
