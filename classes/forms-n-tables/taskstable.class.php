<?php
namespace RAAS\CMS\Social;

use SOME\Namespaces;
use RAAS\Table;

class TasksTable extends Table
{
    public function __get($var)
    {
        switch ($var) {
            case 'view':
                return ViewSub_Dev::i();
                break;
            default:
                return parent::__get($var);
                break;
        }
    }


    public function __construct(array $params = array())
    {
        $view = $this->view;
        $defaultParams = array(
            'columns' => array(
                'material_type_id' => array(
                    'caption' => $this->view->_('MATERIAL_TYPE'),
                    'callback' => function ($row) use ($view) {
                        $text = '<a href="?p=cms&m=social&sub=dev&action=edit_task&id=' . (int)$row->id . '">'
                              .    htmlspecialchars($row->materialType->name)
                              . '</a>';
                        return $text;
                    }
                ),
                'where_to_post' => array(
                    'caption' => $this->view->_('WHERE_TO_POST'),
                    'callback' => function ($row) use ($view) {
                        $where = $row->group->id ? $row->group : $row->profile;
                        $text = '<a href="?p=cms&m=social&sub=dev&action=edit_task&id=' . (int)$row->id . '">' . htmlspecialchars($where->name) . '</a>';
                        return $text;
                    }
                ),
                ' ' => array(
                    'callback' => function ($row) use ($view) {
                        return rowContextMenu($view->getTaskContextMenu($row));
                    }
                )
            ),
        );
        $arr = array_merge($defaultParams, $params);
        parent::__construct($arr);
    }
}
