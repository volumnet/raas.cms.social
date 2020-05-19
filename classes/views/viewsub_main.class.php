<?php
namespace RAAS\CMS\Social;

use RAAS\Application;
use RAAS\CMS\Material_Type;
use RAAS\CMS\Material;
use RAAS\CMS\Page;

class ViewSub_Main extends \RAAS\Abstract_Sub_View
{
    protected static $instance;

    public function tasks(array $IN = array())
    {
        $this->assignVars($IN);
        $this->path[] = array('name' => $this->_('__NAME'), 'href' => self::i()->url);
        $this->title = $this->_('TASKS');
        $this->template = 'tasks_posts';
    }


    public function posts(array $IN = array())
    {
        $this->assignVars($IN);
        $this->path[] = array('name' => $this->_('__NAME'), 'href' => self::i()->url);
        $this->title = $this->_('POSTS');
        $this->template = 'tasks_posts';
    }


    public function task(array $IN = array())
    {
        $IN['Table'] = new MaterialsTable($IN);
        $this->assignVars($IN);
        $this->title = $IN['task']->name;
        $this->path[] = array('name' => $this->_('__NAME'), 'href' => self::i()->url);
        $this->template = $IN['Table']->template;
    }


    public function task_albums(array $IN = array())
    {
        $IN['Table'] = new AlbumsTable($IN);
        $this->assignVars($IN);
        $this->title = $IN['task']->name . ': ' . $this->_('ALBUMS');
        $this->path[] = array('name' => $this->_('__NAME'), 'href' => self::i()->url);
        $this->template = $IN['Table']->template;
        $this->js[] = $this->publicURL . '/task_albums.js';
    }


    public function taskPosts(array $IN = array())
    {
        $IN['Table'] = new PostsTable($IN);
        $this->assignVars($IN);
        $this->title = $IN['task']->name;
        $this->path[] = array('name' => $this->_('__NAME'), 'href' => self::i()->url);
        $this->template = $IN['Table']->template;
    }


    public function socialMenu()
    {
        $tasks = Task::getSet();
        $tasksMenu = $postsMenu = array();
        foreach ($tasks as $task) {
            $taskMenuItem = array(
                'href' => $this->url . '&action=tasks&id=' . (int)$task->id,
                'name' => $task->name,
            );
            if ($task->is_market) {
                $taskMenuItem['submenu'][] = array(
                    'href' => $this->url . '&action=tasks_albums&id=' . (int)$task->id,
                    'name' => $this->_('ALBUMS'),
                );
                $taskMenuItem['submenu'][] = array(
                    'href' => $this->url . '&action=tasks&id=' . (int)$task->id,
                    'name' => $this->_('PRODUCTS'),
                );
            }
            $tasksMenu[] = $taskMenuItem;
            $postsMenu[] = array(
                'href' => $this->url . '&action=posts&id=' . (int)$task->id,
                'name' => $task->name,
            );
        }
        $submenu = array();
        $submenu[] = array(
            'href' => $this->url . '&action=tasks',
            'name' => $this->_('TASKS'),
            'submenu' => $tasksMenu
        );
        $submenu[] = array(
            'href' => $this->url . '&action=posts',
            'name' => $this->_('POSTS'),
            'submenu' => $postsMenu
        );
        return $submenu;
    }


    public function getPostContextMenu(Post $Item)
    {
        $arr = array();
        if ($Item->id) {
            $arr[] = array(
                'href' => $this->url . '&action=update_post&id=' . (int)$Item->id,
                'name' => $this->_('UPDATE'),
                'icon' => 'refresh'
            );
            $arr[] = array(
                'href' => $this->url . '&action=delete_post&id=' . (int)$Item->id . ($edit ? '' : '&back=1'),
                'name' => $this->_('DELETE'),
                'icon' => 'remove',
                'onclick' => 'return confirm(\'' . addslashes(htmlspecialchars($this->_('ARE_YOU_SURE_TO_DELETE_THIS_NOTE'))) . '\')'
            );
        }
        return $arr;
    }


    public function getAllPostsContextMenu()
    {
        $arr = array();
        $arr[] = array(
            'href' => $this->url . '&action=update_post',
            'name' => $this->_('UPDATE'),
            'icon' => 'refresh'
        );
        $arr[] = array(
            'href' => $this->url . '&action=delete_post',
            'name' => $this->_('DELETE'),
            'icon' => 'remove',
            'onclick' => 'return confirm(\'' . addslashes(htmlspecialchars($this->_('DELETE_MULTIPLE_TEXT'))) . '\')'
        );
        return $arr;
    }


    public function getMaterialContextMenu(Material $Item)
    {
        $arr = array();
        if ($Item->id) {
            $arr[] = array(
                'href' => $this->url . '&action=publish&pid=' . (int)$this->nav['id'] . '&id=' . (int)$Item->id,
                'name' => $this->_('PUBLISH'),
                'icon' => 'share'
            );
        }
        return $arr;
    }


    public function getAllMaterialsContextMenu()
    {
        $arr = array();
        $arr[] = array(
            'href' => $this->url . '&action=publish&pid=' . (int)$this->nav['id'],
            'name' => $this->_('PUBLISH'),
            'icon' => 'share'
        );
        return $arr;
    }


    public function getAlbumContextMenu(Page $page, Album $album = null)
    {
        $arr = array();
        $arr[] = array(
            'href' => $this->url . '&action=publish_album&id=' . (int)$page->id . '&pid=' . (int)$this->nav['id'],
            'name' => $this->_('PUBLISH'),
            'icon' => 'share'
        );
        if ($album->id) {
            $arr[] = array(
                'href' => $this->url . '&action=delete_album&album_id=' . (int)$album->id,
                'name' => $this->_('DELETE'),
                'icon' => 'remove',
                'onclick' => 'return confirm(\'' . addslashes(htmlspecialchars($this->_('ARE_YOU_SURE_TO_DELETE_THIS_NOTE'))) . '\')'
            );
        }
        return $arr;
    }


    public function getAllAlbumsContextMenu()
    {
        $arr = array();
        $arr[] = array(
            'href' => $this->url . '&action=publish_album&pid=' . (int)$this->nav['id'],
            'name' => $this->_('PUBLISH'),
            'icon' => 'share'
        );
        if ($album->id) {
            $arr[] = array(
                'href' => $this->url . '&action=delete_album&pid=' . (int)$this->nav['id'],
                'name' => $this->_('DELETE'),
                'icon' => 'remove',
                'onclick' => 'return confirm(\'' . addslashes(htmlspecialchars($this->_('DELETE_MULTIPLE_TEXT'))) . '\')'
            );
        }
        return $arr;
    }
}
