<?php
namespace RAAS\CMS\Social;

use RAAS\CMS\Material;
use RAAS\CMS\Material_Type;
use RAAS\Application;

class Module extends \RAAS\Module
{
    protected static $instance;

    public function __get($var)
    {
        switch ($var) {
            default:
                return parent::__get($var);
                break;
        }
    }


    public function autoload($class)
    {
        require_once $this->includeDir . '/twitteroauth/autoload.php';
        require_once $this->includeDir . '/php-graph-sdk/src/Facebook/autoload.php';
        require_once $this->includeDir . '/vk/src/VK/VK.php';
        require_once $this->includeDir . '/vk/src/VK/VKException.php';
        parent::autoload($class);
    }


    public function getMaterialsByTask(Task $task, array $IN = array())
    {
        $columns = array_filter(
            $task->materialType->fields,
            function ($x) {
                return $x->show_in_table;
            }
        );
        $search_string = $IN['search_string'];
        $sort = $IN['sort'] ?: 'post_date';
        $order = $IN['order'] ?: 'asc';
        $page = (int)$IN['page'] ?: 1;

        $SQL_query = "SELECT SQL_CALC_FOUND_ROWS tM.*
                        FROM " . Material::_tablename() . " AS tM
                   LEFT JOIN " . Post::_tablename() . " AS tP ON tP.task_id = " . (int)$task->id . " AND tP.material_id = tM.id";
        // 2016-01-14, AVS: добавил поиск по данным
        if ($search_string) {
            $SQL_query .= " LEFT JOIN " . Material::_dbprefix() . Material_Field::data_table . " AS tD ON tD.pid = tM.id
                            LEFT JOIN " . Material_Field::_tablename() . " AS tF ON tD.fid = tF.id ";
        }
        $types = array_merge(array($task->materialType->id), (array)$task->materialType->all_children_ids);
        $SQL_query .= " WHERE tM.pid IN (" . implode(", ", $task->materialType->selfAndChildrenIds) . ")
                          AND (NOT tM.show_from OR tM.show_from <= NOW())
                          AND (NOT tM.show_to OR tM.show_to >= NOW())
                          AND ((tP.id IS NULL)" . ($task->check_for_update ? " OR (tM.modify_date > tP.post_date)" : "") . ") ";
        if (($t = strtotime($task->date_from)) > 0) {
            $SQL_query .= " AND tM.modify_date > '" . date('Y-m-d H:i:s', $t) . "'";
        }
        // 2016-01-14, AVS: добавил поиск по данным
        if ($search_string) {
            $SQL_query .= " AND tF.classname = 'RAAS\\\\CMS\\\\Material_Type' AND tF.pid
                            AND (
                                    tM.name LIKE '%" . $this->SQL->real_escape_string($search_string) . "%'
                                 OR tM.urn LIKE '%" . $this->SQL->real_escape_string($search_string) . "%'
                                 OR tD.value LIKE '%" . $this->SQL->real_escape_string($search_string) . "%'
                            )";
        }
        $SQL_query .= " GROUP BY tM.id ";
        $Pages = new \SOME\Pages($page, Application::i()->registryGet('rowsPerPage'));
        if (isset($sort, $columns[$sort]) && ($row = $columns[$sort])) {
            $_sort = $row->urn;
            $Set = Material::getSQLSet($SQL_query);
            if (isset($order) && ($order == 'desc')) {
                $_order = 'desc';
                $reverse = true;
            } else {
                $_order = 'asc';
                $reverse = false;
            }
            $f = Package::i()->getCompareFunction($_sort, $reverse, true);
            usort($Set, $f);
            $Set = \SOME\SOME::getArraySet($Set, $Pages);
        } else {
            switch ($sort) {
                case 'name':
                case 'urn':
                case 'modify_date':
                    $_sort = 'tM.' . $sort;
                    break;
                default:
                    $sort = 'post_date';
                    $_sort = 'tM.post_date';
                    break;
            }
            if (isset($order) && ($order == 'desc')) {
                $_order = 'desc';
            } elseif (!isset($order) && in_array($sort, array('post_date', 'modify_date'))) {
                $_order = 'desc';
            } else {
                $_order = 'asc';
            }

            $SQL_query .= " ORDER BY NOT tM.priority ASC, tM.priority ASC, " . $_sort . " " . strtoupper($_order);
            $Set = Material::getSQLSet($SQL_query, $Pages);
        }
        return array('Set' => $Set, 'Pages' => $Pages, 'sort' => $sort, 'order' => $_order, 'task' => $task, 'mtype' => $task->materialType);
    }


    public function getPostsByTask(Task $task, array $IN = array())
    {
        $columns = array_filter(
            $task->materialType->fields,
            function ($x) {
                return $x->show_in_table;
            }
        );
        $search_string = $IN['search_string'];
        $page = (int)$IN['page'] ?: 1;

        $SQL_query = "SELECT SQL_CALC_FOUND_ROWS tP.*
                        FROM " . Post::_tablename() . " AS tP
                        JOIN " . Material::_tablename() . " AS tM ON tP.task_id = " . (int)$task->id . " AND tP.material_id = tM.id";
        // 2016-01-14, AVS: добавил поиск по данным
        if ($search_string) {
            $SQL_query .= " LEFT JOIN " . Material::_dbprefix() . Material_Field::data_table . " AS tD ON tD.pid = tM.id
                            LEFT JOIN " . Material_Field::_tablename() . " AS tF ON tD.fid = tF.id ";
        }
        $SQL_query .= " WHERE 1 ";
        // 2016-01-14, AVS: добавил поиск по данным
        if ($search_string) {
            $SQL_query .= " AND tF.classname = 'RAAS\\\\CMS\\\\Material_Type' AND tF.pid
                            AND (
                                    tM.name LIKE '%" . $this->SQL->real_escape_string($search_string) . "%'
                                 OR tM.urn LIKE '%" . $this->SQL->real_escape_string($search_string) . "%'
                                 OR tD.value LIKE '%" . $this->SQL->real_escape_string($search_string) . "%'
                            )";
        }
        $SQL_query .= " GROUP BY tM.id ORDER BY tP.post_date DESC ";
        $Pages = new \SOME\Pages($page, Application::i()->registryGet('rowsPerPage'));
        $Set = Post::getSQLSet($SQL_query, $Pages);
        return array('Set' => $Set, 'Pages' => $Pages, 'sort' => $sort, 'order' => $_order, 'task' => $task);
    }


    /**
     * Возвращает привязку страниц к альбомам
     * @param  Task   $task [description]
     * @return array<int $page_id => MarketAlbum>
     */
    public function getAlbumsByTask(Task $task)
    {
        $temp = MarketAlbum::getSet(array('where' => "task_id = " . (int)$task->id));
        $Set = array();
        foreach ($temp as $row) {
            $Set[$row->page_id] = $row;
        }
        return $Set;
    }
}
