<?php

class EasyPics_Plugin_UserAgent extends Zend_Controller_Plugin_Abstract
{

    public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request)
    {
        if (!Zend_Session::sessionExists()) {
            return $this;
        }


        $front = Zend_Controller_Front::getInstance();
        $bootstrap = $front->getParam('bootstrap');

        if (!$bootstrap->hasResource('useragent')) {
            throw new Zend_Controller_Exception('The UserAgent plugin can only be loaded when the UserAgent resource is bootstrapped');
        }

        try {
            $userAgent = $bootstrap->getResource('useragent');

            $device = $userAgent->getDevice();

            $browserType = $userAgent->getBrowserType();
            $features = $device->getAllFeatures();
        }
        catch (Exception $e) {


        }


        Zend_Registry::set("ua_browser_type", $browserType);
        Zend_Registry::set("ua_features", $features);
    }


}
