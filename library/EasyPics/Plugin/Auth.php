<?php
/**
 * Plugin d'authentification
 * 
 * Largement inspiré de :
 * http://julien-pauli.developpez.com/tutoriels/zend-framework/atelier/auth-http/?page=modele-MVC
**/

class EasyPics_Plugin_Auth extends Zend_Controller_Plugin_Abstract
{

    const FAIL_AUTH_MODULE     = 'default';
    const FAIL_AUTH_CONTROLLER = 'index';
    const FAIL_AUTH_ACTION     = 'login';
    const FAIL_ACL_MODULE      = 'default';
    const FAIL_ACL_CONTROLLER  = 'error';
    const FAIL_ACL_ACTION      = 'acl';

    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {

        if (!Zend_Session::isStarted()) {
            Zend_Session::start(true);
        }

        $acl     = new EasyPics_Acl(APPLICATION_PATH . "/conf/acl.ini");
        $auth    = Zend_Auth::getInstance();

        $front   = Zend_Controller_Front::getInstance();
        $default = $front->getDefaultModule();


        $user       = null;
        $role       = 'guest';
        $module 	= $request->getModuleName();
        $controller = $request->getControllerName();
        $action     = $request->getActionName();

        $resource = ($module == $default) ? $resource = $controller : $resource = $module.'_'.$controller;

        if (!$acl->has($resource)) {
            $resource = null;
        }

        if ($auth->hasIdentity()) {
            $user = $auth->getStorage()->read();
            $role = (isset($user->role)) ? $user->role : 'guest';
        }



        /*var_dump($user);
        var_dump($role);
        var_dump($resource);
        var_dump($action);
        var_dump($acl->isAllowed($role, $resource, $action));*/
        //
//exit;
        if (!$acl->isAllowed($role, $resource, $action)) {

            if ($this->_request->isXmlHttpRequest()) {
                $request->setModuleName(self::FAIL_ACL_MODULE);
                $request->setControllerName(self::FAIL_ACL_CONTROLLER);
                $request->setActionName(self::FAIL_ACL_ACTION);
            }
            else {

                $request->setModuleName(self::FAIL_AUTH_MODULE);
                $request->setControllerName(self::FAIL_AUTH_CONTROLLER);
                $request->setActionName(self::FAIL_AUTH_ACTION);
            }
        }
    }
}