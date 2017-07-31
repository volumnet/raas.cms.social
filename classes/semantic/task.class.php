<?php
namespace RAAS\CMS\Social;

use SOME\SOME;
use SOME\Namespaces;
use RAAS\Exception;
use RAAS\CMS\Material;
use RAAS\CMS\Snippet;
use Mustache_Engine;
use SOME\Text;

class Task extends SOME
{
    protected static $tablename = 'cms_social_tasks';
    protected static $defaultOrderBy = "id";
    protected static $references = array(
        'materialType' => array('classname' => 'RAAS\\CMS\\Material_Type', 'FK' => 'material_type_id', 'cascade' => true),
        'profile' => array('classname' => 'RAAS\\CMS\\Social\\Profile', 'FK' => 'profile_id', 'cascade' => true),
        'group' => array('classname' => 'RAAS\\CMS\\Social\\Group', 'FK' => 'group_id', 'cascade' => true),
        'interface' => array('classname' => 'RAAS\\CMS\\Snippet', 'FK' => 'interface_id'),
    );
    protected static $children = array(
        'uploads' => array('classname' => 'RAAS\\CMS\\Social\\Upload', 'FK' => 'task_id'),
        'posts' => array('classname' => 'RAAS\\CMS\\Social\\Post', 'FK' => 'task_id'),
        'images' => array('classname' => 'RAAS\\CMS\\Social\\Image', 'FK' => 'pid'),
        'documents' => array('classname' => 'RAAS\\CMS\\Social\\Document', 'FK' => 'pid'),
    );
    protected static $cognizableVars = array('name');

    public function _name()
    {
        $classname = Namespaces::getClass($this->profile->networkClass);
        $name = $this->materialType->name . ' → '
              . $classname . ': '
              . ($this->group->id ? $this->group->name : $this->profile->name);
        return $name;
    }


    public function publish(array $items)
    {
        foreach ($items as $item) {
            $this->publishItem($item);
        }
    }


    public function publishItem(Material $item, Post $post = null)
    {
        if (!$post) {
            $posts = Post::getSet(array(
                'where' => array("task_id = " . (int)$this->id, "material_id = " . (int)$item->id),
                'orderBy' => "id DESC"
            ));
            if ($posts) {
                $post = array_shift($posts);
            }
        }
        if ($item->pid != $this->material_type_id) {
            throw new Exception('ERROR_PUBLISH_INVALID_MATERIAL_TYPE');
        }
        if ($this->interface->id) {
            $this->interface->process(array('task' => $this, 'material' => $item));
        } else {
            $imagesUploads = $this->getImagesUploads($item, $post);
            $documentUploads = $this->getDocumentUploads($item, $post);
            $post = $this->getPost($item, $imagesUploads, $documentUploads, $post);
            return $post;
        }
    }


    private function getImagesUploads(Material $item, Post $post = null)
    {
        $imagesUploads = array();
        $oldUploads = array();
        if ($post && $post->uploads) {
            foreach ($post->uploads as $oldUpload) {
                if ($oldUpload->upload_type == 'image') {
                    $oldUploads[(int)$oldUpload->attachment_id] = $oldUpload;
                }
            }
        }
        if ($this->images) {
            foreach ($this->images as $image) {
                $materialImages = (array)$item->fields[$image->field->urn]->getValues(true);
                if ($image->max_count) {
                    $materialImages = array_slice($materialImages, 0, $image->max_count);
                }
                foreach ($materialImages as $materialImage) {
                    if ($oldUploads[$materialImage->id]) {
                        // string чтобы сохранить порядок
                        $imagesUploads[(string)$materialImage->id] = $oldUploads[$materialImage->id];
                    } elseif ($uploadData = $this->profile->network->uploadImage($materialImage, $this)) {
                        $upload = new Upload(array(
                            'iid' => trim($uploadData['id']),
                            'url' => trim($uploadData['url']),
                            'task_id' => (int)$this->id,
                            'attachment_id' => (int)$materialImage->id,
                            'upload_type' => 'image',
                            'material_id' => (int)$item->id,
                            'group_id' => (int)$this->group_id,
                            'profile_id' => (int)$this->profile_id,
                            'post_date' => date('Y-n-d H:i:s')
                        ));
                        $upload->commit();
                        // string чтобы сохранить порядок
                        $imagesUploads[(string)$materialImage->id] = $upload;
                    }
                }
            }
        }
        $uploadsToDelete = array_diff_key($oldUploads, $imagesUploads);
        foreach ($uploadsToDelete as $uploadToDelete) {
            Upload::delete($uploadToDelete);
        }
        $imagesUploads = array_values($imagesUploads);
        return $imagesUploads;
    }


    private function getDocumentUploads(Material $item, Post $post = null)
    {
        $documentUploads = array();
        $oldUploads = array();
        if ($post && $post->uploads) {
            foreach ($post->uploads as $oldUpload) {
                if ($oldUpload->upload_type == 'document') {
                    $oldUploads[(int)$oldUpload->attachment_id] = $oldUpload;
                }
            }
        }
        if ($this->documents) {
            foreach ($this->documents as $document) {
                $materialDocuments = (array)$item->fields[$document->field->urn]->getValues(true);
                if ($document->max_count) {
                    $materialDocuments = array_slice($materialDocuments, 0, $document->max_count);
                }
                foreach ($materialDocuments as $materialDocument) {
                    if ($oldUploads[$materialDocument->id]) {
                        // string чтобы сохранить порядок
                        $documentUploads[(string)$materialDocument->id] = $oldUploads[$materialDocument->id];
                    } elseif ($uploadData = $this->profile->network->uploadDocument($materialDocument, $this)) {
                        $upload = new Upload(array(
                            'iid' => trim($uploadData['id']),
                            'url' => trim($uploadData['url']),
                            'task_id' => (int)$this->id,
                            'attachment_id' => (int)$materialDocument->id,
                            'upload_type' => 'document',
                            'material_id' => (int)$item->id,
                            'group_id' => (int)$this->group_id,
                            'profile_id' => (int)$this->profile_id,
                            'post_date' => date('Y-n-d H:i:s')
                        ));
                        $upload->commit();
                        // string чтобы сохранить порядок
                        $documentUploads[(string)$materialDocument->id] = $upload;
                    }
                }
            }
        }
        $uploadsToDelete = array_diff_key($oldUploads, $documentUploads);
        foreach ($uploadsToDelete as $uploadToDelete) {
            Upload::delete($uploadToDelete);
        }
        $documentUploads = array_values($documentUploads);
        return $documentUploads;
    }


    public function getPost(Material $item, array $imagesUploads, array $documentUploads, Post $post = null)
    {
        $text = $this->getText($item);
        if ($postData = $this->profile->network->uploadText($this, $text, $imagesUploads, $documentUploads, $post)) {
            if (!$post) {
                $post = new Post(array(
                    'task_id' => (int)$this->id,
                    'material_id' => (int)$item->id,
                    'group_id' => (int)$this->group_id,
                    'profile_id' => (int)$this->profile_id,
                ));
            }
            $post->iid = trim($postData['id']);
            $post->url = trim($postData['url']);
            $post->post_date = date('Y-n-d H:i:s');
            $post->name = Text::cuttext($postData['name'], 128, '...');
            $post->commit();
            foreach ($imagesUploads as $imagesUpload) {
                $imagesUpload->post_id = $post->id;
                $imagesUpload->commit();
            }
            foreach ($documentUploads as $documentUpload) {
                $documentUpload->post_id = $post->id;
                $documentUpload->commit();
            }
            return $post;
        }
    }


    private function getText(Material $item)
    {
        $m = new Mustache_Engine();
        $data = array(
            'name' => $item->name,
            'description' => strip_tags($item->description),
            'url' => 'http' . ($_SERVER['HTTPS'] == 'on' ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $item->url
        );
        foreach ($item->fields as $field) {
            if (!in_array($field->datatype, array('material', 'image', 'file'))) {
                $val = $field->doRich();
                if (is_array($val)) {
                    $val = implode(', ', $val);
                }
                $data[$field->urn] = $val;
            }
        }
        $text = $m->render($this->description, $data);
        return $text;
    }
}
