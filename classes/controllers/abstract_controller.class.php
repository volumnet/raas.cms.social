<?php
namespace RAAS\CMS\Social;

abstract class Abstract_Controller extends \RAAS\Abstract_Module_Controller
{
    protected static $instance;

    protected function execute()
    {
        switch ($this->sub) {
            case 'dev':
                parent::execute();
                break;
            default:
                Sub_Main::i()->run();
                break;
        }
    }


    public function config()
    {
        return array(
            array('type' => 'text', 'name' => 'facebook_app_id', 'caption' => $this->view->_('FACEBOOK_APP_ID')),
            array('type' => 'text', 'name' => 'facebook_app_secret', 'caption' => $this->view->_('FACEBOOK_APP_SECRET')),
            array('type' => 'text', 'name' => 'twitter_app_id', 'caption' => $this->view->_('TWITTER_APP_ID')),
            array('type' => 'text', 'name' => 'twitter_app_secret', 'caption' => $this->view->_('TWITTER_APP_SECRET')),
            array('type' => 'text', 'name' => 'vk_app_id', 'caption' => $this->view->_('VK_APP_ID')),
            array('type' => 'text', 'name' => 'vk_app_secret', 'caption' => $this->view->_('VK_APP_SECRET')),
        );
    }
}
