<?php
namespace RAAS\CMS\Social;

use RAAS\Redirector;
use RAAS\Exception;
use RAAS\CMS\Material_Type;
use RAAS\StdSub;
use RAAS\CMS\Material;
use RAAS\CMS\Page;

class Sub_Main extends \RAAS\Abstract_Sub_Controller
{
    protected static $instance;

    public function run()
    {
        $this->view->submenu = $this->view->socialMenu();
        switch ($this->action) {
            case 'tasks':
            case 'tasks_albums':
            case 'posts':
                $this->{$this->action}();
                break;
            case 'delete_post':
                $ids = (array)$_GET['id'];
                $items = array_map(function ($x) {
                    return new Post((int)$x);
                }, $ids);
                $items = array_values($items);
                StdSub::delete($items, isset($_GET['back']) ? 'history:back' : $this->url . '&action=posts');
                break;
            case 'update_post':
                $ids = (array)$_GET['id'];
                $items = array_map(function ($x) {
                    return new Post((int)$x);
                }, $ids);
                $items = array_values($items);
                StdSub::update($items, isset($_GET['back']) ? 'history:back' : $this->url . '&action=posts');
                break;
            case 'publish':
                if ($_GET['pid']) {
                    $task = Task::spawn($_GET['pid']);
                }
                if (!$task->id) {
                    $tasks = Task::getSet();
                    new Redirector($this->url . '&action=tasks' . ($tasks ? '&id=' . $tasks[0]->id : ''));
                }
                $ids = (array)$_GET['id'];
                $items = array_map(function ($x) {
                    return new Material((int)$x);
                }, $ids);
                $items = array_values($items);
                $task->publish($items);
                new Redirector(isset($_GET['back']) ? 'history:back' : ($this->url . '&action=tasks&id=' . (int)$task->id));
                break;
            case 'publish_album':
                if ($_GET['pid']) {
                    $task = Task::spawn($_GET['pid']);
                }
                if (!$task->id) {
                    $tasks = Task::getSet();
                    new Redirector($this->url . '&action=tasks' . ($tasks ? '&id=' . $tasks[0]->id : ''));
                }
                $ids = (array)$_GET['id'];
                $items = array_map(function ($x) {
                    return new Page((int)$x);
                }, $ids);
                $items = array_values($items);
                $task->publishAlbums($items);
                new Redirector(isset($_GET['back']) ? 'history:back' : ($this->url . '&action=tasks_albums&id=' . (int)$task->id));
                break;
            case 'delete_album':
                $ids = (array)$_GET['id'];
                $items = array_map(function ($x) {
                    return new MarketAlbum((int)$x);
                }, $ids);
                $items = array_values($items);
                StdSub::delete($items, isset($_GET['back']) ? 'history:back' : $this->url . '&action=tasks_albums&id=' . (int)$task->id);
                break;
            default:
                $tasks = Task::getSet();
                new Redirector($this->url . '&action=tasks' . ($tasks ? '&id=' . $tasks[0]->id : ''));
                break;
        }
    }


    protected function tasks()
    {
        if ($_GET['id']) {
            $task = Task::spawn($_GET['id']);
        }
        if (!$task->id) {
            $tasks = Task::getSet();
            if ($tasks) {
                new Redirector($this->url . '&action=tasks&id=' . $tasks[0]->id);
            } else {
                $this->view->tasks();
                return;
            }
        }
        $OUT = Module::i()->getMaterialsByTask($task, $_GET);
        $this->view->task($OUT);
    }


    protected function tasks_albums()
    {
        if ($_GET['id']) {
            $task = Task::spawn($_GET['id']);
        }
        if (!$task->id) {
            $tasks = Task::getSet();
            if ($tasks) {
                new Redirector($this->url . '&action=tasks_albums&id=' . $tasks[0]->id);
            } else {
                $this->view->tasks();
                return;
            }
        }
        $OUT = array();
        $OUT['task'] = $task;
        $OUT['page'] = $task->rootPage;
        $OUT['albums'] = Module::i()->getAlbumsByTask($task);
        $this->view->task_albums($OUT);
    }


    protected function posts()
    {
        if ($_GET['id']) {
            $task = Task::spawn($_GET['id']);
        }
        if (!$task->id) {
            $tasks = Task::getSet();
            if ($tasks) {
                new Redirector($this->url . '&action=posts&id=' . $tasks[0]->id);
            } else {
                $this->view->posts();
                return;
            }
        }
        $OUT = Module::i()->getPostsByTask($task, $_GET);
        $this->view->taskPosts($OUT);
    }
}
