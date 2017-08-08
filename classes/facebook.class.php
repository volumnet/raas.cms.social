<?php
namespace RAAS\CMS\Social;

use RAAS\CMS\Material;
use RAAS\Attachment;
use Facebook\Facebook as FacebookConnection;
use Facebook\Exceptions\FacebookSDKException;
use RAAS\Exception;
use SOME\Text;
use \CURLFile;
use \RAAS\CMS\Page;

class Facebook extends Network
{
    protected $user;

    public function uploadImage(Attachment $attachment, Task $task)
    {
        try {
            $connection = self::getConnection();
            if ($task->group->id) {
                if (self::getImagesCount($task) <= 1) {
                    $OUT = array(
                        'id' => '0',
                        'url' => self::getAttachmentUrl($attachment)
                    );
                    return $OUT;
                }
                $url = '/' . $task->group->iid . '/photos';
            } else {
                $url = '/' . $task->profile->iid . '/photos';
            }
            $accessToken = self::getAccessToken($task);
            $data = array(
                'source' => $connection->fileToUpload($attachment->file),
            );
            if (!$task->group->id || ($task->group->id != 'group')) {
                $data['published'] = (bool)$task->group->id;
            }
            if ($task->group->id && $task->post_as_profile) {
                $data['no_story'] = true;
            }
            $response = $connection->post($url, $data, $accessToken);
            $response = $response->getGraphNode()->asArray();
            $id = $response['id'];
            $OUT = array(
                'id' => $id
            );

            $OUT['url'] = self::getImageUrl($response['id'], $accessToken);
            return $OUT;
        } catch (FacebookSDKException $e) {
            // print_r ($e); exit;
            throw new Exception('ERROR_PUBLISH');
        }
    }


    public function uploadDocument(Attachment $attachment, Task $task)
    {
        $OUT = array(
            'id' => '0',
            'url' => self::getAttachmentUrl($attachment)
        );
        return $OUT;
    }


    public function uploadText(Task $task, $text, array $imagesUploads = array(), array $documentUploads = array(), Post $post = null)
    {
        try {
            $onlyOnePhoto = ($task->group->id && (self::getImagesCount($task) <= 1) && $imagesUploads);
            $connection = self::getConnection();
            $accessToken = self::getAccessToken($task);
            $data = array(
                'message' => $text,
            );
            if ($documentUploads) {
                $data['message'] .= "\n";
                foreach ($documentUploads as $documentUpload) {
                    $data['message'] .= "\n" . $documentUpload->url;
                }
            }
            if ($post->id) {
                $url = '/' . $post->iid;
                $response = $connection->post($url, $data, $accessToken);
                $response = $response->getGraphNode()->asArray();
                if ($response['success']) {
                    return array(
                        'id' => $post->iid,
                        'url' => $post->url,
                        'name' => Text::cuttext(preg_replace('/(\\r\\n)|\\n|\\r/i', ' ', $text), 128, '...')
                    );
                } else {
                    throw new Exception('ERROR_PUBLISH');
                }
            } else {
                if ($task->group->id) {
                    if ($onlyOnePhoto) {
                        $url = '/' . $task->group->iid . '/photos';
                        $data['source'] = $connection->fileToUpload($imagesUploads[0]->attachment->file);
                        if ($task->post_as_profile) {
                            $data['no_story'] = true;
                        }
                    } else {
                        $url = '/' . $task->group->iid . '/feed';
                    }
                } else {
                    $url = '/' . $task->profile->iid . '/feed';
                    for ($i = 0; $i < count($imagesUploads); $i++) {
                        $data['attached_media[' . $i . ']'] = json_encode(array('media_fbid' => $imagesUploads[$i]->iid));
                    }
                }
                $response = $connection->post($url, $data, $accessToken);
                $response = $response->getGraphNode()->asArray();
                if ($response) {
                    $OUT = array();
                    if ($onlyOnePhoto) {
                        $upload = $imagesUploads[0];
                        $upload->iid = trim($response['id']);
                        $upload->url = self::getImageUrl($response['id'], $accessToken);
                        $upload->commit();
                        $OUT['id'] = $response['post_id'];
                    } else {
                        $OUT['id'] = $response['id'];
                    }
                    $OUT['url'] = 'https://facebook.com/' . $OUT['id'];
                    $OUT['name'] = Text::cuttext(preg_replace('/(\\r\\n)|\\n|\\r/i', ' ', $text), 128, '...');
                    return $OUT;
                } else {
                    throw new Exception('ERROR_PUBLISH');
                }
            }
        } catch (FacebookSDKException $e) {
            // print_r ($e); exit;
            throw new Exception('ERROR_PUBLISH');
        }
    }


    public function deletePost(Post $post)
    {
        try {
            $connection = self::getConnection();
            $task = $post->task;
            $onlyOnePhoto = ($task->group->id && (self::getImagesCount($task) <= 1) && $post->uploads);
            $accessToken = self::getAccessToken($task);
            foreach ($post->uploads as $upload) {
                if (($upload->upload_type == 'image') && $upload->iid && !preg_match('/_' . preg_quote($upload->iid) . '/i', $post->iid)) {
                    $response = $connection->delete('/' . $upload->iid, array(), $accessToken);
                }
            }
            $response = $connection->delete('/' . $post->iid, array(), $accessToken);
            $graphObject = $response->getGraphObject()->asArray();
        } catch (FacebookSDKException $e) {
            // print_r ($e); exit;
        }
        return true;
    }


    public static function auth(array $IN = array())
    {
        try {
            $connection = self::getConnection();
            $helper = $connection->getRedirectLoginHelper();
            $accessToken = $helper->getAccessToken();
            if (!isset($accessToken)) {
                throw new Exception('ERROR_ADD_PROFILE');
            }
            $oAuth2Client = $connection->getOAuth2Client();
            $tokenMetadata = $oAuth2Client->debugToken($accessToken);
            $tokenMetadata->validateAppId(self::getAppId());
            $tokenMetadata->validateExpiration();
            if (!$accessToken->isLongLived()) {
                $accessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
            }
            $response = $connection->get('/me?fields=id,name,link,picture', (string)$accessToken);
            $user = json_decode($response->getBody());
            $profile = static::getMatchingProfile($user->id);
            $profile->urn = trim($user->id);
            $profile->url = trim($user->link);
            $profile->name = trim($user->name);
            $profile->avatar = trim($user->picture->data->url);
            $profile->access_token = (string)$accessToken;
            $profile->expiration_date = $accessToken->getExpiresAt() ? $accessToken->getExpiresAt()->format('Y-m-d H:i:s') : '0000-00-00';
            $profile->commit();
            return $profile;
        } catch (FacebookSDKException $e) {
            throw new Exception('ERROR_ADD_PROFILE');
        }
    }


    public function getGroup($url)
    {
        if (!preg_match('/^http(s)?:\\/\\//umi', $url)) {
            $url = 'http://' . $url;
        }
        $urn = trim(parse_url($url, PHP_URL_PATH), '/');
        $urn = explode('/', $urn);
        try {
            $connection = self::getConnection();
            if (mb_strtolower($urn[0]) == 'groups') {
                // Группа
                $urn = trim($urn[1]);
                if (is_numeric($urn)) {
                    $id = $urn;
                } else {
                    $search = $connection->get('/search?q=' . $urn . '&type=group', $this->profile->access_token);
                    $search = json_decode($search->getBody());
                    if ($search->data) {
                        $id = (int)$search->data[0]->id;
                    } else {
                        throw new Exception('ERROR_ADD_GROUP');
                    }
                }
                $response = $connection->get('/' . $id . '?fields=id,name,picture', $this->profile->access_token);
                $response = json_decode($response->getBody());
                $group = static::getMatchingGroup($response->id);
                $group->urn = trim($urn);
                $group->url = trim('https://facebook.com/groups/' . $id);
                $group->name = trim($response->name);
                $group->avatar = trim($response->picture->data->url);
                $group->group_type = 'group';
            } else {
                // Страница
                $urn = trim($urn[0]);
                $response = $connection->get('/' . $urn . '?fields=id,name,access_token,link,picture', $this->profile->access_token);
                $response = json_decode($response->getBody());
                $group = static::getMatchingGroup($response->id);
                $group->urn = trim($urn);
                $group->url = trim($response->link);
                $group->name = trim($response->name);
                $group->avatar = trim($response->picture->data->url);
                $group->group_type = 'page';
                $group->access_token = trim($response->access_token);
            }
            $group->commit();
            return $group;
        } catch (FacebookSDKException $e) {
            throw new Exception('ERROR_ADD_GROUP');
        }
    }


    public static function getLoginUrl($callback)
    {
        try {
            $connection = self::getConnection();
            $helper = $connection->getRedirectLoginHelper();
            $permissions = array(
                'public_profile',
                'user_posts',
                'manage_pages',
                'publish_pages',
                'publish_actions',
                'user_managed_groups',
            );
            $url = $helper->getLoginUrl($callback, $permissions);
            return $url;
        } catch (FacebookSDKException $e) {
            // throw new Exception('ERROR_FACEBOOK_INVALID_SETTINGS');
        }
    }


    private static function getConnection()
    {
        $appId = self::getAppId();
        $appSecret = self::getAppSecret();
        $connection = new FacebookConnection(array('app_id' => $appId, 'app_secret' => $appSecret, 'default_graph_version' => 'v2.9'));
        return $connection;
    }


    private static function getAppId()
    {
        return Module::i()->registryGet('facebook_app_id') ?: '';
    }


    private static function getAppSecret()
    {
        return Module::i()->registryGet('facebook_app_secret') ?: '';
    }


    private static function getImagesCount(Task $task)
    {
        $c = 0;
        foreach ($task->images as $image) {
            $c += $image->max_count;
        }
        return $c;
    }


    private static function getImageUrl($id, $accessToken)
    {
        try {
            $connection = self::getConnection();
            $response = $connection->get('/' . $id . '?fields=images', $accessToken);
            $response = $response->getGraphNode()->asArray();
            $response = $response['images'];
            $width = 0;
            $imageUrl = '';
            foreach ($response as $image) {
                if ($image['width'] > $width) {
                    $width = $image['width'];
                    $imageUrl = $image['source'];
                }
            }
            return $imageUrl;
        } catch (FacebookSDKException $e) {
            // print_r ($e); exit;
            throw new Exception('ERROR_PUBLISH');
        }
    }


    private static function getAccessToken(Task $task)
    {
        try {
            $connection = self::getConnection();
            if ($task->group->id && ($task->group->group_type != 'group') && !$task->post_as_profile) {
                $response = $connection->get('/' . $task->group->iid . '?fields=access_token', $task->profile->access_token);
                $response = $response->getGraphNode()->asArray();
                $accessToken = $response['access_token'];
            } else {
                $accessToken = $task->profile->access_token;
            }
            return $accessToken;
        } catch (FacebookSDKException $e) {
            // print_r ($e); exit;
            throw new Exception('ERROR_PUBLISH');
        }
    }
}
