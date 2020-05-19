<?php
namespace RAAS\CMS\Social;

use RAAS\CMS\Page;
use RAAS\Form;
use RAAS\FormTab;
use RAAS\CMS\Material_Type;
use RAAS\FieldSet;
use RAAS\CMS\Material_Field;
use SOME\Namespaces;
use RAAS\CMS\Snippet_Folder;
use RAAS\Option;
use RAAS\CMS\Page_Field;

class EditTaskForm extends \RAAS\Form
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
        $Item = isset($params['Item']) ? $params['Item'] : null;
        $materialType = isset($params['materialType']) ? $params['materialType'] : null;
        $materialTypeFields = array();
        $dataHint = array(
            '{{name}} — ' . $this->view->_('NAME'),
            '{{description}} — ' . $this->view->_('DESCRIPTION'),
            '{{url}} — ' . $this->view->_('URL'),
        );
        foreach ($materialType->fields as $field) {
            $materialTypeFields[$field->datatype][] = array('value' => $field->id, 'caption' => $field->name);
            if (($field->datatype == 'number') && ($field->urn == 'price')) {
                $defaultMaterialPriceField = $field->id;
            }
            if (!in_array($field->datatype, array('material', 'image', 'file'))) {
                $dataHint[] = '{{' . $field->urn . '}} — ' . $field->name;
            }
        }
        $profiles = Profile::getSet(array('orderBy' => 'url'));
        $profilesSet = array();
        foreach ($profiles as $profile) {
            $classname = Namespaces::getClass($profile->networkClass);
            $profilesSet[] = array('value' => $profile->id, 'caption' => $classname . ': ' . $profile->name, 'data-network' => $classname);
        }
        $profile = $Item->profile->id ? $Item->profile : $profiles[0];
        $groupsSet = Group::getSet();
        $groupsSet = array_filter($groupsSet, function ($x) use ($profile) {
            return $x->networkClass == $profile->networkClass;
        });
        $wf = function (Snippet_Folder $x) use (&$wf) {
            $temp = array();
            foreach ($x->children as $row) {
                if (strtolower($row->urn) != '__raas_views') {
                    $o = new Option(array('value' => '', 'caption' => $row->name, 'disabled' => 'disabled'));
                    $o->__set('children', $wf($row));
                    $temp[] = $o;
                }
            }
            foreach ($x->snippets as $row) {
                $temp[] = new Option(array('value' => $row->id, 'caption' => $row->name));
            }
            return $temp;
        };
        $temp = Page_Field::getSet(array('orderBy' => 'priority'));
        $pageFields = array(
            'name' => array(),
            'image' => array()
        );
        foreach ($temp as $row) {
            if (in_array($row->datatype, array('text'))) {
                $pageFields['name'][] = array('value' => (int)$row->id, 'caption' => $row->name);
            } elseif (in_array($row->datatype, array('image'))) {
                $pageFields['image'][] = array('value' => (int)$row->id, 'caption' => $row->name);
                if (in_array($row->urn, array('image', 'images')) && !$defaultPageImageField) {
                    $defaultPageImageField = $row->id;
                }
            }
        }
        if (!$defaultPageImageField && $pageFields['image']) {
            $defaultPageImageField = $pageFields['image'][0]['value'];
        }
        if (!$defaultMaterialPriceField && $materialTypeFields['number']) {
            $defaultMaterialPriceField = $materialTypeFields['number'][0]['value'];
        }

        $marketCats = array();
        if ($Item->is_market) {
            $temp = $profile->network->getMarketCategories();
            $marketCats = array();
            switch (Namespaces::getClass($profile->networkClass)) {
                case 'Vk':
                    foreach ($temp as $row) {
                        $section = array('caption' => '-- ' . $row['name'] . ' --', 'disabled' => true, 'style' => 'font-weight: bold;', 'children' => array());
                        foreach ($row['children'] as $row2) {
                            $section['children'][] = array('value' => $row2['id'], 'caption' => str_repeat(json_decode('\u00a0'), 3) . $row2['name']);
                        }
                        $marketCats[] = $section;
                    }
                    break;
                case 'Facebook':
                    break;
            }
        }


        $p = new Page(0);
        $defaultParams = array(
            'caption' => $this->view->_($Item->id ? 'EDIT_TASK' : 'ADD_TASK') . ': ' . $materialType->name,
            'parentUrl' => Sub_Dev::i()->url . '&action=social',
            'actionMenu' => false,
            'children' => array(
                'common' => new FormTab(array(
                    'name' => 'common',
                    'caption' => $this->view->_('COMMON'),
                    'children' => array(
                        'material_type_id' => array(
                            'caption' => $this->view->_('MATERIAL_TYPE'),
                            'readonly' => true,
                            'import' => function () use ($materialType) {
                                return $materialType->name;
                            },
                            'default' => $materialType->name,
                            'export' => null,
                        ),
                        'profile_id' => array(
                            'type' => 'select',
                            'class' => 'input-xxlarge',
                            'name' => 'profile_id',
                            'caption' => $this->view->_('PROFILE'),
                            'required' => true,
                            'children' => $profilesSet,
                        ),
                        'group_id' => array(
                            'type' => 'select',
                            'class' => 'input-xxlarge',
                            'name' => 'group_id',
                            'caption' => $this->view->_('GROUP'),
                            'placeholder' => '--',
                            'children' => array('Set' => $groupsSet),
                        ),
                        'post_as_profile' => array(
                            'type' => 'checkbox',
                            'name' => 'post_as_profile',
                            'caption' => $this->view->_('POST_AS_PROFILE'),
                        ),
                        'is_market' => array(
                            'type' => 'checkbox',
                            'name' => 'is_market',
                            'caption' => $this->view->_('IS_MARKET'),
                        ),
                        'interface_id' => array(
                            'type' => 'select',
                            'class' => 'input-xxlarge',
                            'name' => 'interface_id',
                            'caption' => $view->_('INTERFACE'),
                            'placeholder' => '--',
                            'children' => $wf(new Snippet_Folder()),
                        ),
                        'check_for_update' => array(
                            'type' => 'checkbox',
                            'name' => 'check_for_update',
                            'caption' => $this->view->_('CHECK_FOR_UPDATE'),
                        ),
                        'date_from' => array(
                            'type' => 'datetime',
                            'name' => 'date_from',
                            'caption' => $this->view->_('PUBLISH_MATERIALS_AFTER_DATE'),
                        ),
                        'description' => array(
                            'type' => 'textarea',
                            'class' => 'input-xxlarge',
                            'name' => 'description',
                            'caption' => $this->view->_('POST_TEXT'),
                            'data-hint' => implode("\n", $dataHint),
                            'template' => 'edit_task_description.inc.php',
                        ),
                    ),
                )),
                'market' => new FormTab(array(
                    'name' => 'market',
                    'caption' => $this->view->_('MARKET'),
                    'children' => array(
                        'root_page_id' => array(
                            'type' => 'select',
                            'name' => 'root_page_id',
                            'caption' => $this->view->_('ROOT_PAGE'),
                            'children' => array('Set' => $p->children)
                        ),
                        'category_id' => array(
                            'type' => 'select',
                            'name' => 'category_id',
                            'caption' => $this->view->_('CATEGORY'),
                            'children' => $marketCats
                        ),
                        'name_field_id' => array(
                            'type' => 'select',
                            'name' => 'name_field_id',
                            'caption' => $this->view->_('NAME_FIELD'),
                            'children' => $materialTypeFields['text'],
                            'placeholder' => $this->view->_('NAME')
                        ),
                        'marker_field_id' => array(
                            'type' => 'select',
                            'name' => 'marker_field_id',
                            'caption' => $this->view->_('MARKER_FIELD'),
                            'children' => $materialTypeFields['checkbox'],
                            'placeholder' => $this->view->_('_NONE')
                        ),
                        'price_field_id' => array(
                            'type' => 'select',
                            'name' => 'price_field_id',
                            'caption' => $this->view->_('PRICE_FIELD'),
                            'children' => $materialTypeFields['number'],
                            'required' => true,
                            'default' => $defaultMaterialPriceField,
                        ),
                        'album_name_field_id' => array(
                            'type' => 'select',
                            'name' => 'album_name_field_id',
                            'caption' => $this->view->_('ALBUM_NAME_FIELD'),
                            'children' => $pageFields['name'],
                            'placeholder' => $this->view->_('NAME'),
                        ),
                        'album_image_field_id' => array(
                            'type' => 'select',
                            'name' => 'album_image_field_id',
                            'caption' => $this->view->_('ALBUM_IMAGE_FIELD'),
                            'children' => $pageFields['image'],
                            'placeholder' => $this->view->_('_NONE'),
                            'default' => $defaultPageImageField,
                        ),
                    ),
                )),
            ),
            'export' => function (Form $f) use ($materialType) {
                $f->exportDefault();
                $f->Item->material_type_id = $materialType->id;
            }
        );
        foreach (array('images', 'documents') as $key) {
            if ($keyChildren = $this->getFieldsChildren($key, $materialType, $Item)) {
                $defaultParams['children']['common']->children[$key] = new FieldSet(array(
                    'name' => $key,
                    'caption' => $this->view->_(mb_strtoupper($key)),
                    'import' => $this->getFieldsImport($key),
                    'export' => null,
                    'oncommit' => $this->getFieldsOnCommit($key),
                    'children' => $keyChildren,
                ));
            }
        }
        $arr = array_merge($defaultParams, $params);
        parent::__construct($arr);
    }


    protected function getFieldsImport($key)
    {
        return function (FieldSet $fieldSet) use ($key) {
            $Item = $fieldSet->Form->Item;
            $DATA = array();
            foreach ($Item->$key as $row) {
                $DATA[$key][$row->fid] = $row->max_count;
                $DATA[$key . '[' . (int)$row->fid . ']'] = $row->max_count;
            }
            return $DATA;
        };
    }


    protected function getFieldsOnCommit($key)
    {
        $classname = 'RAAS\\CMS\\Social\\' . ucfirst(preg_replace('/s$/umi', '', $key));
        $datatype = ($key == 'images') ? 'image' : 'file';
        return function (FieldSet $fieldSet) use ($key, $classname, $datatype) {
            $Item = $fieldSet->Form->Item;
            $priority = 1;
            foreach ($_POST[$key] as $fid => $maxCount) {
                $field = new Material_Field($fid);
                if ($field->datatype == $datatype) {
                    $affected = $classname::getSet(array('where' => array("fid = " . $fid, "pid = " . $Item->id)));
                    if (count($affected) > 1) {
                        for ($i = 1; $i < count($affected); $i++) {
                            $classname::delete($affected[$i]);
                        }
                    }
                    $affected = $affected ? $affected[0] : null;
                    if (trim($maxCount) !== '') {
                        if (!$affected) {
                            $affected = new $classname(array('pid' => (int)$Item->id, 'fid' => (int)$fid));
                        }
                        $affected->max_count = (int)$maxCount;
                        $affected->priority = $priority;
                        $affected->commit();
                    } else {
                        if ($affected) {
                            $classname::delete($affected);
                        }
                    }
                }
                $priority++;
            }
        };
    }


    protected function getFieldsChildren($key, Material_Type $materialType, Task $Item)
    {
        $arr = array();
        $datatype = ($key == 'images') ? 'image' : 'file';
        foreach ((array)$Item->$key as $val) {
            $arr[$key . '[' . (int)$val->fid . ']'] = array(
                'type' => 'number',
                'name' => $key . '[' . (int)$val->fid . ']',
                'caption' => $val->field->name,
                'data-hint' => $this->view->_('MAX_FILES'),
                'data-role' => 'upload-counter',
            );
        }
        foreach ($materialType->fields as $val) {
            if (!isset($arr[$key . '[' . (int)$val->id . ']']) && ($val->datatype == $datatype)) {
                $arr[$key . '[' . (int)$val->id . ']'] = array(
                    'type' => 'number',
                    'name' => $key . '[' . (int)$val->id . ']',
                    'caption' => $val->name,
                    'data-hint' => $this->view->_('MAX_FILES'),
                    'data-role' => 'upload-counter',
                );
            }
        }
        return $arr;
    }
}
