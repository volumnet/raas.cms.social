<?php
namespace RAAS\CMS\Social;

use RAAS\Form;
use RAAS\Field;
use RAAS\Exception;
use RAAS\Redirector;

class AddVKForm extends Form
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
        $defaultParams = array(
            'caption' => $this->view->_('ADD_VK_PROFILE'),
            'parentUrl' => Sub_Dev::i()->url . '&action=social',
            'actionMenu' => false,
            'children' => array(
                'access_url' => array(
                    'name' => 'access_url',
                    'caption' => $this->view->_('ACCESS_URL'),
                    'required' => true,
                    'import' => null,
                    'default' => $materialType->name,
                    'check' => function (Field $field) use ($view) {
                        $localError = $field->getErrors();
                        if (!$localError) {
                            $returnURL = $view->url . '&action=social';
                            try {
                                $accessToken = parse_url($_POST['access_url'], PHP_URL_FRAGMENT);
                                parse_str($accessToken, $arr);
                                $accessToken = $arr['access_token'];
                                if ($arr['access_token']) {
                                    VK::auth($arr);
                                    new Redirector($returnURL);
                                }
                                parse_str($_POST['access_url'], $arr);
                                $accessToken = $arr['access_token'];
                                if ($arr['access_token']) {
                                    VK::auth($arr);
                                    new Redirector($returnURL);
                                }
                                $accessToken = $_POST['access_url'];
                                VK::auth(array('access_token' => $accessToken));
                                new Redirector($returnURL);
                            } catch (Exception $e) {
                                $localError[] = array(
                                    'name' => 'INVALID',
                                    'value' => 'access_token',
                                    'description' => $this->view->_('ACCESS_TOKEN_INVALID')
                                );
                            }
                        }
                        return $localError;
                    },
                ),
            ),
            'import' => null,
            'export' => null,
            'commit' => null,
            'template' => 'add_vk.tmp.php'
        );
        $arr = array_merge($defaultParams, $params);
        parent::__construct($arr);
    }
}
