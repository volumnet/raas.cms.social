<?php
namespace RAAS\CMS\Social;

use \RAAS\Application;

class ViewSub_Dev extends \RAAS\Abstract_Sub_View
{
    protected static $instance;

    public function edit_task(array $IN = array())
    {
        $this->assignVars($IN);
        $this->title = $IN['Form']->caption;
        $this->path[] = array('name' => $this->_('DEVELOPMENT'), 'href' => self::i()->url);
        $this->path[] = array('name' => $this->_('__NAME'), 'href' => self::i()->url . '&action=social');
        $this->contextmenu = $this->getTaskContextMenu($IN['Item']);
        $this->title = $IN['Form']->caption;
        $this->template = $IN['Form']->template;
        $this->js[] = $this->publicURL . '/edit_task.js';
    }


    public function social(array $IN = array())
    {
        $IN['profilesTable'] = new ProfilesTable(array('Set' => $IN['profiles']));
        $IN['groupsTable'] = new GroupsTable(array('Set' => $IN['groups']));
        $IN['tasksTable'] = new TasksTable(array('Set' => $IN['tasks']));
        $this->assignVars($IN);
        $this->title = $this->_('__NAME');
        $this->path[] = array('name' => $this->_('DEVELOPMENT'), 'href' => self::i()->url);
        $this->template = 'social.tmp.php';
    }


    public function add_vk(array $IN = array())
    {
        $this->assignVars($IN);
        $this->title = $IN['Form']->caption;
        $this->path[] = array('name' => $this->_('DEVELOPMENT'), 'href' => self::i()->url);
        $this->path[] = array('name' => $this->_('__NAME'), 'href' => self::i()->url . '&action=social');
        $this->title = $IN['Form']->caption;
        $this->template = $IN['Form']->template;
    }


    public function devMenu()
    {
        $submenu = array();
        $submenu[] = array(
            'href' => $this->url . '&action=social',
            'name' => $this->_('__NAME'),
            'active' => (in_array($this->action, array('social', 'edit_task')))
        );
        return $submenu;
    }


    public function getTaskContextMenu(Task $Item)
    {
        $arr = array();
        if ($Item->id) {
            $edit = ($this->action == 'edit_task');
            if (!$edit) {
                $arr[] = array(
                    'href' => $this->url . '&action=edit_task&id=' . (int)$Item->id,
                    'name' => $this->_('EDIT'),
                    'icon' => 'pencil'
                );
            }
            $arr[] = array(
                'href' => $this->url . '&action=delete_task&id=' . (int)$Item->id . ($edit ? '' : '&back=1'),
                'name' => $this->_('DELETE'),
                'icon' => 'remove',
                'onclick' => 'return confirm(\'' . addslashes(htmlspecialchars($this->_('ARE_YOU_SURE_TO_DELETE_THIS_NOTE'))) . '\')'
            );
        }
        return $arr;
    }
}
