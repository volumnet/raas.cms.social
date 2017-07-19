<?php
namespace RAAS\CMS\Social;

use RAAS\CMS\Material;
use RAAS\Attachment;
use RAAS\Exception;

abstract class Network
{
    protected $profile;

    public function __construct(Profile $profile)
    {
        $this->profile = $profile;
    }


    public static function auth(array $IN = array())
    {
        $networkId = $IN['network'];
        switch ($networkId) {
            case 'vk':
                Vk::auth($IN);
                break;
            case 'facebook':
                Facebook::auth($IN);
                break;
            case 'twitter':
                Twitter::auth($IN);
                break;
            default:
                throw new Exception('ERROR_ADD_PROFILE_INVALID_NETWORK');
                break;
        }
    }


    public static function getNetwork($url)
    {
        if (stristr($url, 'facebook.com')) {
            return 'RAAS\CMS\Social\Facebook';
        } elseif (stristr($url, 'twitter.com')) {
            return 'RAAS\CMS\Social\Twitter';
        } elseif (stristr($url, 'vk.com') || stristr($url, 'vkontakte')) {
            return 'RAAS\CMS\Social\Vk';
        }
        return null;
    }


    public static function addGroup($url)
    {
        $classname = static::getNetwork($url);
        if ($classname) {
            $profiles = Profile::getSet();
            $profiles = array_filter($profiles, function ($x) use ($classname) {
                return $x->networkClass == $classname;
            });
            if (!$profiles) {
                throw new Exception('ERROR_ADD_GROUP_NO_PROFILE');
            }
            foreach ($profiles as $profile) {
                if ($group = $profile->network->getGroup($url)) {
                    return $group;
                }
            }
            throw new Exception('ERROR_ADD_GROUP_NO_RIGHTS');
        }
        throw new Exception('ERROR_ADD_GROUP_INVALID_NETWORK');
    }


    public static function getMatchingProfile($id)
    {
        $matchingProfiles = Profile::getSet(array(
            'where' => array(
                "iid = '" . Profile::_SQL()->real_escape_string(trim($id)) . "'",
            )
        ));
        $classname = get_called_class();
        $matchingProfiles = array_filter($matchingProfiles, function ($x) use ($classname) {
            return $x->networkClass == $classname;
        });
        if ($matchingProfiles) {
            $profile = array_shift($matchingProfiles);
        } else {
            $profile = new Profile(array('iid' => trim($id)));
        }
        return $profile;
    }


    public static function getMatchingGroup($id)
    {
        $matchingGroups = Group::getSet(array(
            'where' => array(
                "iid = '" . Group::_SQL()->real_escape_string(trim($id)) . "'",
            )
        ));
        $classname = get_called_class();
        $matchingGroups = array_filter($matchingGroups, function ($x) use ($classname) {
            return $x->networkClass == $classname;
        });
        if ($matchingGroups) {
            $group = array_shift($matchingGroups);
        } else {
            $group = new Group(array('iid' => trim($id)));
        }
        return $group;
    }

    abstract public function getGroup($url);

    abstract public static function getLoginUrl($callback);

    abstract public function uploadImage(Attachment $attachment, Task $task);

    abstract public function uploadDocument(Attachment $attachment, Task $task);

    abstract public function uploadText(Task $task, $text, array $imagesUploads = array(), array $documentUploads = array(), Post $post = null);

    abstract public function deletePost(Post $post);
}
