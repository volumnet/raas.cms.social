<?php
namespace RAAS\CMS\Social;

use RAAS\CMS\Package;

class View_Web extends \RAAS\Module_View_Web
{
    protected static $instance;

    public function header()
    {
        $this->css[] = $this->publicURL . '/style.css';
        $menuItems = array(
            array(
                'href' => $this->url . '&sub=main',
                'name' => $this->_('SOCIALS'),
                'active' => ($this->moduleName == 'shop') && ($this->sub != 'dev')
            )
        );
        $menu = $this->menu->getArrayCopy();
        array_splice($menu, -1, 0, $menuItems);
        $this->menu = new \ArrayObject($menu);
    }
}
