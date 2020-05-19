<?php
namespace RAAS\CMS\Social;

use SOME\Namespaces;
use RAAS\CMS\Package;
use RAAS\Table;
use RAAS\CMS\Page;

class AlbumsTable extends Table
{
    public function __get($var)
    {
        switch ($var) {
            case 'view':
                return ViewSub_Main::i();
                break;
            default:
                return parent::__get($var);
                break;
        }
    }


    public function __construct(array $params = array())
    {
        $view = $this->view;
        $f = function (Page $node) use (&$f) {
            static $level = 0;
            $Set = array();
            foreach ($node->children as $row) {
                $row->level = $level;
                $Set[] = $row;
                $level++;
                $Set = array_merge($Set, $f($row));
                $level--;
            }
            return $Set;
        };
        $defaultParams = array(
            'Set' => $f($params['page']),
            'meta' => array(
                'allValue' => 'ids',
                'allContextMenu' => $this->view->getAllAlbumsContextMenu()
            ),
            'template' => 'cms/multitable.tmp.php',
            'emptyString' => $this->view->_('NO_NOTES_FOUND'),
            'data-role' => 'multitable'
        );
        $columns = array();
        $columns['name'] = array(
            'caption' => $this->view->_('NAME'),
            'callback' => function ($row) use ($view, $params) {
                $album = $params['albums'][$row->id];
                return '<a style="padding-left: ' . ($row->level * 30) . 'px" data-level="' . (int)$row->level . '" href="' . Package::i()->view->url . '&id=' . (int)$row->id . '" target="_blank" ' . (!$album ? 'class="muted"' : '') . '>'
                     .    htmlspecialchars($row->name)
                     . '</a>';
            }
        );
        $columns['album'] = array(
            'caption' => $this->view->_('ALBUM'),
            'callback' => function ($row) use ($view, $params) {
                if ($album = $params['albums'][$row->id]) {
                    return '<a href="' . $album->url . '" target="_blank">'
                         .    htmlspecialchars($album->name)
                         . '</a>';
                }
            }
        );
        $columns[' '] = array(
            'callback' => function ($row) use ($view, $params) {
                $album = $params['albums'][$row->id];
                return rowContextMenu($view->getAlbumContextMenu($row, $album));
            }
        );
        $defaultParams['columns'] = $columns;
        $arr = $defaultParams;
        parent::__construct($arr);
    }
}
