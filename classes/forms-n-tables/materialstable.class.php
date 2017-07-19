<?php
namespace RAAS\CMS\Social;

use SOME\Namespaces;
use RAAS\CMS\Package;

class MaterialsTable extends \RAAS\CMS\MaterialsTable
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
        parent::__construct($params);
        unset($this->columns['priority']);
        foreach ($this->columns as $key => $col) {
            if ($key == 'name') {
                $this->columns['name']->callback = function ($row) use ($view, $params, $pidText) {
                    return '<a href="' . Package::i()->view->url . '&action=edit_material&id=' . (int)$row->id . $pidText . '" ' . (!$row->vis ? 'class="muted"' : '') . ' target="_blank">'
                         .    htmlspecialchars($row->name)
                         . '</a>';
                };
            } elseif ($params['mtype']->fields[$key]->datatype == 'material') {
                $this->columns[$key]->callback = function ($row) use ($col, $view) {
                    $f = $row->fields[$key];
                    $v = $f->getValue();
                    $m = new Material($v);
                    if ($m->id) {
                        return '<a href="' . Package::i()->view->url . '&action=edit_material&id=' . (int)$m->id . '" ' . (!$m->vis ? 'class="muted"' : '') . ' target="_blank">'
                             .    htmlspecialchars($m->name)
                             . '</a>';
                    }
                };
            } elseif ($params['mtype']->fields[$key]->datatype == 'image') {
                $this->columns[$key]->callback = function ($row) use ($key, $view, $params, $pidText) {
                    $f = $row->fields[$key];
                    $v = $f->getValue();
                    if ($v->id) {
                        return '<a href="' . Package::i()->view->url . '&action=edit_material&id=' . (int)$row->id . $pidText . '" ' . (!$row->vis ? 'class="muted"' : '') . ' target="_blank">
                                  <img src="/' . $v->tnURL . '" style="max-width: 48px;" /></a>';
                    }
                };
            }
        }

        $this->meta['allValue'] = null;
        $this->meta['allContextMenu'] = $this->view->getAllMaterialsContextMenu();
        $this->template = 'cms/multitable.tmp.php';
        $this->emptyString = $this->view->_('NO_NOTES_FOUND');
        foreach ($this->columns as $column) {
            $column->sortable = null;
        }
    }
}
