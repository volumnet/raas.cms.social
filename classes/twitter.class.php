<?php
namespace RAAS\CMS\Social;

use RAAS\CMS\Material;
use RAAS\Attachment;
use Abraham\TwitterOAuth\TwitterOAuth;
use Abraham\TwitterOAuth\TwitterOAuthException;
use RAAS\Exception;
use SOME\Text;

class Twitter extends Network
{
    private $connection;

    public function uploadImage(Attachment $attachment, Task $task)
    {
        try {
            $connection = self::getConnection($task->profile->access_token, $task->profile->token_secret);

            $response = $connection->upload('media/upload', array('media' => $attachment->file));
            $OUT = array(
                'id' => $response->media_id_string,
                'url' => self::getAttachmentUrl($attachment)
            );

            return $OUT;
        } catch (TwitterOAuthException $e) {
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
            $connection = self::getConnection($task->profile->access_token, $task->profile->token_secret);
            if ($post->id) {
                $this->deletePost($post);
                $post = null;
            }
            $data = array(
                'status' => $text,
            );
            if ($documentUploads) {
                $data['status'] .= "\n";
                foreach ($documentUploads as $documentUpload) {
                    $data['status'] .= "\n" . $documentUpload->url;
                }
            }
            if ($imagesUploads) {
                $mediaIds = array();
                foreach ($imagesUploads as $imagesUpload) {
                    $mediaIds[] .= $documentUpload->iid;
                }
                $data['media_ids'] = implode(
                    ',',
                    array_map(
                        function ($x) {
                            return $x->iid;
                        },
                        $imagesUploads
                    )
                );
            }
            $response = $connection->post('statuses/update', $data);
            foreach ((array)$response->extended_entities->media as $media) {
                foreach ($imagesUploads as $imagesUpload) {
                    if ($imagesUpload->iid == $media->id_str) {
                        $imagesUpload->url = $media->media_url_https;
                        $imagesUpload->commit();
                        break;
                    }
                }
            }
            $OUT = array(
                'id' => $response->id_str,
                'url' => $task->profile->url . '/status/' . $response->id_str,
                'name' => Text::cuttext(preg_replace('/(\\r\\n)|\\n|\\r/i', ' ', $text), 128, '...'),
            );
            return $OUT;
        } catch (TwitterOAuthException $e) {
            throw new Exception('ERROR_PUBLISH');
        }
    }


    public function deletePost(Post $post)
    {
        try {
            $connection = self::getConnection($post->task->profile->access_token, $post->task->profile->token_secret);
            $response = $connection->post('statuses/destroy/' . $post->iid, array());
            // print_r ($response); exit;
        } catch (TwitterOAuthException $e) {
            // print_r ($e); exit;
        }
        return true;
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
            $user = $connection->get('account/verify_credentials');
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
        $connection->setTimeouts(30, 30);
        return $connection;
    }
}
