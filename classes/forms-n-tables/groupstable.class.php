<?php
namespace RAAS\CMS\Social;

use SOME\Namespaces;
use RAAS\Table;

class GroupsTable extends Table
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
                'avatar' => array(
                    'caption' => $this->view->_('AVATAR'),
                    'callback' => function ($row) use ($view) {
                        $classname = strtolower(Namespaces::getClass($row->networkClass));
                        $text = '<a href="' . $row->url . '" target="blank" class="cms-social-profile__image cms-social-profile__image_' . $classname . (!$row->avatar ? ' cms-social-profile__image_no-avatar' : '') . '">';
                        if ($row->avatar) {
                            $text .= '<img src="' . $row->avatar . '" alt="' . htmlspecialchars($row->name) . '" title="' . htmlspecialchars($row->name) . '" />';
                        }
                        $text .= '</a>';
                        return $text;
                    }
                ),
                'name' => array(
                    'caption' => $this->view->_('NAME'),
                    'callback' => function ($row) use ($view) {
                        $classname = strtolower(Namespaces::getClass($row->networkClass));
                        $text = '<a href="' . $row->url . '" target="blank">' . htmlspecialchars($row->name) . '</a>';
                        return $text;
                    }
                ),
                ' ' => array(
                    'callback' => function ($row) use ($view) {
                        $text = '<a class="icon icon-remove" title="' . $view->_('DELETE') . '" href="' . $view->url . '&action=delete_group&id=' . (int)$row->id . '" onclick="return confirm(\'' . addslashes(htmlspecialchars($view->_('ARE_YOU_SURE_TO_DELETE_THIS_NOTE'))) . '\')"></a>';
                        return $text;
                    }
                )
            ),
        );
        $arr = array_merge($defaultParams, $params);
        parent::__construct($arr);
    }
}
