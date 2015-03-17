<?php
class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{

    protected function _initConfig()
    {
        $config = new Zend_Config_Ini(
            realpath(APPLICATION_PATH . '/conf/settings.ini'),
            APPLICATION_ENV
        );
        Zend_Registry::set("config", $config);
    }

    protected function _initRESTFull()
    {
        $front = Zend_Controller_Front::getInstance();
        $router = $front->getRouter();

        //exit;
        $restRoute = new Zend_Rest_Route($front, array(), array(
            'api' => array('album', 'picture'),
            'gallery' => array('album', 'picture')
        ));
        $router->addRoute('rest', $restRoute);
    }

    protected function _initContext()
    {
        $contextSwitch = new Zend_Controller_Action_Helper_ContextSwitch();
        $contextSwitch->addContext('text', array (
            'suffix' => 'text',
            'headers' => array('Content-Type' => 'text/plain; charset=utf-8')
        ));
        $contextSwitch->addContext('jpeg', array (
            'suffix' => 'jpeg',
            'headers' => array('Content-Type' => 'image/jpeg')
        ));
        Zend_Controller_Action_HelperBroker::addHelper($contextSwitch);
    }

}
