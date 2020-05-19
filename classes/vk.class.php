<?php
namespace RAAS\CMS\Social;

use RAAS\CMS\Material;
use RAAS\Attachment;
use VK\VK as VkConnection;
use VK\VKException;
use RAAS\Exception;
use SOME\HTTP;
use SOME\Text;
use \CURLFile;

class Vk extends Network implements Marketable
{
    public function uploadImage(Attachment $attachment, Task $task)
    {
        try {
            $data = array();
            if ($task->group->id) {
                $data['group_id'] = $task->group->iid;
            }
            $connection = self::getConnection($this->profile->access_token);
            $response = $connection->api('photos.getWallUploadServer', $data);
            if (!$response['response']) {
                throw new Exception('ERROR_PUBLISH');
            }
            $response = $response['response'];
            $url = $response['upload_url'];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt(
                $ch,
                CURLOPT_POSTFIELDS,
                array('photo' => class_exists('CURLFile') ? new CURLFile($attachment->file) : '@' . $attachment->file)
            );
            $result = curl_exec($ch);
            curl_close($ch);
            $response = json_decode($result);
            $response->photo = stripslashes($response->photo);

            $data = array(
                'photo' => $response->photo,
                'server' => $response->server,
                'hash' => $response->hash,
                'caption' => $attachment->name
            );
            if ($task->group->id) {
                $data['group_id'] = $task->group->iid;
            } else {
                $data['user_id'] = $task->profile->iid;
            }
            $response = $connection->api('photos.saveWallPhoto', $data);
            if (!$response['response'][0]) {
                throw new Exception('ERROR_PUBLISH');
            }
            $response = $response['response'][0];
            $OUT = array(
                'id' => $response['owner_id'] . '_' . $response['id']
            );
            foreach (array('2560', '1280', '807', '604', '130', '75') as $key) {
                if ($response['photo_' . $key]) {
                    $OUT['url'] = $response['photo_' . $key];
                    break;
                }
            }
            return $OUT;
        } catch (VKException $e) {
            throw new Exception('ERROR_PUBLISH');
        }
    }


    public function uploadDocument(Attachment $attachment, Task $task)
    {
        try {
            $data = array();
            if ($task->group->id) {
                $data['group_id'] = $task->group->iid;
            }
            $connection = self::getConnection($this->profile->access_token);
            $response = $connection->api('docs.getWallUploadServer', $data);
            if (!$response['response']) {
                throw new Exception('ERROR_PUBLISH');
            }
            $response = $response['response'];
            $url = $response['upload_url'];
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt(
                $ch,
                CURLOPT_POSTFIELDS,
                array('file' => class_exists('CURLFile') ? new CURLFile($attachment->file) : '@' . $attachment->file)
            );
            $result = curl_exec($ch);
            curl_close($ch);
            $response = json_decode($result);

            $data = array(
                'file' => $response->file,
                'title' => $attachment->name
            );
            $response = $connection->api('docs.save', $data);
            if (!$response['response'][0]) {
                throw new Exception('ERROR_PUBLISH');
            }
            $response = $response['response'][0];
            $OUT = array(
                'id' => $response['owner_id'] . '_' . $response['id'],
                'url' => $response['url']
            );
            return $OUT;
        } catch (VKException $e) {
            throw new Exception('ERROR_PUBLISH');
        }
    }


    public function uploadText(Task $task, $text, array $imagesUploads = array(), array $documentUploads = array(), Post $post = null)
    {
        try {
            $data = array(
                'owner_id' => $task->group->id ? '-' . $task->group->iid : $task->profile->iid,
                'from_group' => $task->post_as_profile ? 0 : 1,
                'message' => $text,
            );
            $attachments = array();
            foreach ($imagesUploads as $imageUpload) {
                $attachments[] = 'photo' . $imageUpload->iid;
            }
            foreach ($documentUploads as $documentUpload) {
                $attachments[] = 'doc' . $documentUpload->iid;
            }
            if ($attachments) {
                $data['attachments'] = implode(',', $attachments);
            }
            $connection = self::getConnection($this->profile->access_token);
            if ($post->id) {
                $data['post_id'] = $post->iid;
                $response = $connection->api('wall.edit', $data);
                if (!$response['response']) {
                    throw new Exception('ERROR_PUBLISH');
                }
                $response = $response['response'];
                if (is_numeric($response) && ($response > 0)) {
                    return array(
                        'id' => $post->iid,
                        'url' => $post->url,
                        'name' => Text::cuttext(preg_replace('/(\\r\\n)|\\n|\\r/i', ' ', $text), 128, '...')
                    );
                } else {
                    throw new Exception('ERROR_PUBLISH');
                }
            } else {
                $response = $connection->api('wall.post', $data);
                if (!$response['response']) {
                    throw new Exception('ERROR_PUBLISH');
                }
                $response = $response['response']['post_id'];
                if (is_numeric($response) && ($response > 0)) {
                    return array(
                        'id' => $response,
                        'url' => 'https://vk.com/wall' . ($task->group->id ? '-' . $task->group->iid : $task->profile->iid) . '_' . $response,
                        'name' => Text::cuttext(preg_replace('/(\\r\\n)|\\n|\\r/i', ' ', $text), 128, '...')
                    );
                } else {
                    throw new Exception('ERROR_PUBLISH');
                }
            }
        } catch (VKException $e) {
            throw new Exception('ERROR_PUBLISH');
        }
    }


    public function deletePost(Post $post)
    {
        try {
            $data = array(
                'owner_id' => $post->task->group->id ? '-' . $post->task->group->iid : $post->task->profile->iid,
                'post_id' => $post->iid,
            );
            $connection = self::getConnection($this->profile->access_token);
            $response = $connection->api('wall.delete', $data);
            if (!$response['response']) {
                throw new Exception('ERROR_PUBLISH');
            }
            $response = $response['response'];
        } catch (VKException $e) {
        }
        return true;
    }


    public function getMarketCategories()
    {
        try {
            $connection = self::getConnection($this->profile->access_token);
            $response = $connection->api('market.getCategories', array('count' => 1000));
            if (!$response['response']) {
                throw new Exception('ERROR_GETTING_CATEGORIES');
            }
            $response = $response['response'];
            $cats = array();
            foreach ($response['items'] as $row) {
                if (!$cats[$row['section']['id']]) {
                    $cats[$row['section']['id']] = array(
                        'id' => $row['section']['id'],
                        'name' => $row['section']['name'],
                        'children' => array()
                    );
                }
                $cats[$row['section']['id']]['children'][] = array('id' => $row['id'], 'name' => $row['name']);
            }
            return $cats;
        } catch (VKException $e) {
        }
    }


    public static function auth(array $IN = array())
    {
        try {
            $connection = self::getConnection();
            // if (!$connection->isAuth()) {
            //     throw new Exception('ERROR_ADD_PROFILE');
            // }
            if (!$connection->checkAccessToken($IN['access_token'])) {
                throw new Exception('ERROR_ADD_PROFILE');
            }
            $connection = self::getConnection($IN['access_token']);
            $response = $connection->api('users.get', array('fields' => 'nickname,domain,photo_50'));
            $user = $response['response'][0];
            $profile = static::getMatchingProfile($user['id']);
            $profile->urn = trim($user['domain']);
            $profile->url = trim('https://vk.com/' . $user['domain']);
            $name = array();
            foreach (array('first_name', 'nickname', 'last_name') as $key) {
                if ($user[$key]) {
                    $name[] = $user[$key];
                }
            }
            if ($name) {
                $profile->name = implode(' ', $name);
            }
            $profile->avatar = trim($user['photo_50']);
            $profile->access_token = $IN['access_token'];
            $profile->expiration_date = $IN['expires_in'] ? date('Y-m-d H:i:s', time() + $IN['expires_in']) : '0000-00-00';
            $profile->commit();
            return $profile;
        } catch (VKException $e) {
            throw new Exception('ERROR_ADD_PROFILE');
        }
    }


    public function getGroup($url)
    {
        if (!preg_match('/^http(s)?:\\/\\//umi', $url)) {
            $url = 'http://' . $url;
        }
        $urn = trim(parse_url($url, PHP_URL_PATH), '/');
        $urn = trim(array_shift(explode('/', $urn)));
        try {
            $connection = self::getConnection($this->profile->access_token);
            $response = $connection->api('groups.getById', array('group_ids' => $urn));
            if (!$response['response']) {
                throw new Exception('ERROR_ADD_GROUP');
            }
            $response = $response['response'][0];
            $group = static::getMatchingGroup($response['id']);
            $group->urn = trim($response['screen_name']);
            $group->url = trim('https://vk.com/' . $response['screen_name']);
            $group->name = trim($response['name']);
            $group->avatar = trim($response['photo_50']);
            $group->commit();
            return $group;
        } catch (VKException $e) {
            throw new Exception('ERROR_ADD_GROUP');
        }
    }


    public function uploadMarketImage(Attachment $attachment, MarketTask $task)
    {
        // @todo
        return array('id' => 0, 'url' => '#attachment=' . $attachment->id . ';task=' . $task->id);
    }


    public function uploadMarketAlbum(MarketTask $task, $name, MarketAlbum $album = null)
    {
        // @todo
        return array('id' => 0, 'url' => '#task=' . $task->id . ';album=' . $album->id, 'name' => $name);
    }


    public static function getLoginUrl($callback)
    {
        try {
            $connection = self::getConnection();
            $url = $connection->getAuthorizeURL('photos,wall,groups,offline,docs,market', $callback);
            return $url;
        } catch (VKException $e) {
        }
    }


    private static function getConnection($token = null)
    {
        $appId = Module::i()->registryGet('vk_app_id') ?: '6042784';
        $appSecret = Module::i()->registryGet('vk_app_secret') ?: 'LPIvkWAanUzMGF5r6oDD';
        $connection = new VkConnection($appId, $appSecret, $token);
        $connection->setApiVersion('5.64');
        return $connection;
    }
}
