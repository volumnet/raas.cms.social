<?php
namespace RAAS\CMS\Social;

use RAAS\CMS\Material;
use RAAS\Attachment;
use Abraham\TwitterOAuth\TwitterOAuth;
use Abraham\TwitterOAuth\TwitterOAuthException;
use RAAS\Exception;

class Twitter extends Network
{
    private $connection;

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
        if (isset($IN['oauth_token']) && ($_SESSION['twitter_request_token']['oauth_token'] !== $IN['oauth_token'])) {
            throw new Exception('ERROR_ADD_PROFILE');
        }

        try {
            $connection = self::getConnection($_SESSION['twitter_request_token']['oauth_token'], $_SESSION['twitter_request_token']['oauth_token_secret']);
            $accessToken = $connection->oauth("oauth/access_token", array("oauth_verifier" => $IN['oauth_verifier']));
            $connection = self::getConnection($accessToken['oauth_token'], $accessToken['oauth_token_secret']);
            $user = $connection->get("account/verify_credentials");
            $profile = static::getMatchingProfile($user->id_str);
            $profile->urn = trim($user->screen_name);
            $profile->url = 'https://twitter.com/' . trim($user->screen_name);
            $profile->name = trim($user->name);
            $profile->avatar = trim($user->profile_image_url_https);
            $profile->access_token = $accessToken['oauth_token'];
            $profile->token_secret = $accessToken['oauth_token_secret'];
            $profile->expiration_date = '0000-00-00';
            $profile->commit();
            return $profile;
        } catch (TwitterOAuthException $e) {
            throw new Exception('ERROR_ADD_PROFILE');
        }
    }


    public function getGroup($url)
    {
        return false;
    }


    public static function getLoginUrl($callback)
    {
        try {
            $connection = self::getConnection();
            $request_token = $connection->oauth('oauth/request_token', array('oauth_callback' => $callback));
            $_SESSION['twitter_request_token'] = $request_token;
            $url = $connection->url('oauth/authorize', array('oauth_token' => $request_token['oauth_token']));
            return $url;
        } catch (TwitterOAuthException $e) {
            throw new Exception('ERROR_TWITTER_INVALID_SETTINGS');
        }
    }


    private static function getConnection($token = null, $secret = null)
    {
        $appId = Module::i()->registryGet('twitter_app_id') ?: 'L0uzWh2IGv3bvnW8tbEyBQdH6';
        $appSecret = Module::i()->registryGet('twitter_app_secret') ?: 'tJWlSSZY672SU010J33RtyoUJchufzp5LMSxvvi9F5Cw0Uylbs';
        $connection = new TwitterOAuth($appId, $appSecret, $token, $secret);
        return $connection;
    }
}
