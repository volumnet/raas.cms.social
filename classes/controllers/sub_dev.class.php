<?php
namespace RAAS\CMS\Social;

use RAAS\Redirector;
use RAAS\Exception;
use RAAS\CMS\Material_Type;
use RAAS\StdSub;

class Sub_Dev extends \RAAS\Abstract_Sub_Controller
{
    protected static $instance;

    public function run()
    {
        $this->view->submenu = \RAAS\CMS\ViewSub_Dev::i()->devMenu();
        switch ($this->action) {
            case 'edit_task':
            case 'social':
            case 'add_vk':
                $this->{$this->action}();
                break;
            case 'delete_profile':
                $ids = (array)$_GET['id'];
                $items = array_map(function ($x) {
                    return new Profile((int)$x);
                }, $ids);
                $items = array_values($items);
                StdSub::delete($items, $this->url . '&action=social');
                break;
            case 'delete_group':
                $ids = (array)$_GET['id'];
                $items = array_map(function ($x) {
                    return new Group((int)$x);
                }, $ids);
                $items = array_values($items);
                StdSub::delete($items, $this->url . '&action=social');
                break;
            case 'delete_task':
                $ids = (array)$_GET['id'];
                $items = array_map(function ($x) {
                    return Task::spawn((int)$x);
                }, $ids);
                $items = array_values($items);
                StdSub::delete($items, $this->url . '&action=social');
                break;
            default:
                new Redirector(\RAAS\CMS\ViewSub_Dev::i()->url);
                break;
        }
    }


    protected function edit_task()
    {
        $Item = Task::spawn((int)$this->id);
        $materialType = $Item->id ? $Item->materialType : new Material_Type($_GET['pid']);
        $Form = new EditTaskForm(array('materialType' => $materialType, 'Item' => $Item));
        $this->view->edit_task($Form->process());
    }


    protected function add_vk()
    {
        $Form = new AddVKForm(array());
        $this->view->add_vk($Form->process($Form->process()));
    }


    protected function social()
    {
        $localError = array();
        if ($_GET['auth']) {
            try {
                Network::auth($_GET);
                new Redirector($this->url . '&action=social');
            } catch (Exception $e) {
                $localError[] = array(
                    'name' => $e->getMessage(),
                    'value' => 'add_group',
                    'description' => $this->view->_($e->getMessage())
                );
            }
        }
        if ($_POST['add_group']) {
            $url = $_POST['add_group'];
            if (!preg_match('/^http(s)?:\\/\\//umi', $url)) {
                $url = 'http://' . $url;
            }
            try {
                Network::addGroup($url);
            } catch (Exception $e) {
                $localError[] = array(
                    'name' => $e->getMessage(),
                    'value' => 'add_group',
                    'description' => $this->view->_($e->getMessage())
                );
            }
        }
        $profiles = Profile::getSet();
        $groups = Group::getSet();
        $tasks = Task::getSet();
        $materialTypes = Material_Type::getSet();
        $selfUrl = 'http' . ($_SERVER['HTTPS'] == 'on' ? 's' : '') . '://'
                 . $_SERVER['HTTP_HOST']
                 . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
                 . $this->url
                 . '&action=social&auth=1';
        $urls = array();
        try {
            if ($url = Facebook::getLoginUrl($selfUrl . '&network=facebook')) {
                $urls['facebook'] = $url;
            }
        } catch (Exception $e) {
            $localError[] = array(
                'name' => $e->getMessage(),
                'value' => 'facebook_app_id',
                'description' => $this->view->_($e->getMessage())
            );
        }
        try {
            if ($url = Twitter::getLoginUrl($selfUrl . '&network=twitter')) {
                $urls['twitter'] = $url;
            }
        } catch (Exception $e) {
            $localError[] = array(
                'name' => $e->getMessage(),
                'value' => 'twitter_app_id',
                'description' => $this->view->_($e->getMessage())
            );
        }
        try {
            if ($url = $this->url . '&action=add_vk') {
                $urls['vk'] = $url;
            }
        } catch (Exception $e) {
            $localError[] = array(
                'name' => $e->getMessage(),
                'value' => 'vk_app_id',
                'description' => $this->view->_($e->getMessage())
            );
        }
        $this->view->social(array(
            'profiles' => $profiles,
            'groups' => $groups,
            'tasks' => $tasks,
            'materialTypes' => $materialTypes,
            'loginUrls' => $urls,
            'localError' => $localError
        ));
    }
}
