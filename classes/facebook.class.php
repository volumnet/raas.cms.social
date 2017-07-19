<?php
namespace RAAS\CMS\Social;

use RAAS\CMS\Material;
use RAAS\Attachment;
use Facebook\Facebook as FacebookConnection;
use Facebook\Exceptions\FacebookSDKException;
use RAAS\Exception;

class Facebook extends Network
{
    protected $user;

    public function uploadImage(Attachment $attachment, Task $task)
    {
        // @todo
    }


    public function uploadDocument(Attachment $attachment, Task $task)
    {
        // @todo
    }


    public function uploadText(Task $task, $text, array $imagesUploads = array(), array $documentUploads = array(), Post $post = null)
    {
        // @todo
    }


    public function deletePost(Post $post)
    {
        // @todo
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
            $permissions = array('public_profile', 'user_posts', 'manage_pages', 'publish_pages');
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
}
