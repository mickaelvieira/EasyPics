<?php
class SettingsController extends EasyPics_Controller_Action
{

    public function indexAction()
    {
        $config = Zend_Registry::get('config');
        $this->_helper->layout->setLayout("settings");

        $this->view->Header()->setTitle($config->app->name);
        $this->view->Stylesheet()->setPaths(array(
            "public/css/easypics.css"
        ));
        $this->view->Javascript()->setPaths(array(
            "public/js/libs/head.js",
            "public/js/scripts/easypics.js"
        ));
    }
}