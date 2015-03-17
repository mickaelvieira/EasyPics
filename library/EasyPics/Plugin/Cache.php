<?php
class EasyPics_Plugin_Cache extends Zend_Controller_Plugin_Abstract
{


    public static $doNotCache = false;


    public $cache;


    public $key;


    public $_excludeContentType = array(
        "application/json",
        "image/jpeg",
        "image/jpg",
        "image/gif",
        "image/png",
        "application/xml",
        "text/xml"
    );

    public function __construct()
    {
        $config = EasyPics::getAppConfig();

        if (is_null($config)) {
            return $this;
        }

        $cacheDir      = $config->cache->backendOptions->cache_dir;
        $cacheLifetime = $config->cache->frontendOptions->lifetime;
        $serialize     = $config->cache->frontendOptions->automatic_serialization;

        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }

        $frontendOptions = array(
            'lifetime' => $cacheLifetime,
            'automatic_serialization' => $serialize
        );
        $backendOptions = array(
            'cache_dir' => $cacheDir
        );

        $this->cache = Zend_Cache::factory(
            'Core',
            'File',
            $frontendOptions,
            $backendOptions
        );

    }

    public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request)
    {
        $config = EasyPics::getAppConfig();

        if (!is_null($config) || !$request->isGet() || !$config->cache->enable) {
            self::$doNotCache = true;
            return;
        }

        $this->key = $this->_getCacheKey($request);

        if (false !== ($response = $this->cache->load($this->key))) {

            EasyPics::log("Get From Cache ".$this->_uncrypted);

            $response->sendResponse();
            exit;
        }
    }

    public function dispatchLoopShutdown()
    {
        $response = $this->getResponse();
        $headers  = $response->getHeaders();

        if (is_array($headers)) {

            foreach ($headers as $key => $header) {

                $name  = $header['name'];
                $value = $header['value'];

                if ($name == "Content-Type") {

                    if (in_array($value, $this->_excludeContentType)) {
                        self::$doNotCache = true;
                    }
                    break;
                }
            }
        }

        if (self::$doNotCache || $this->getResponse()->isRedirect() || (null === $this->key)) {
            return;
        }

        EasyPics::log("Build Cache ".$this->_uncrypted);

        $this->cache->save($this->getResponse(), $this->key);
    }

    protected function _getCacheKey($request)
    {
        $user = EasyPics::getUser();

        $module 	= $request->getModuleName();
        $controller = $request->getControllerName();
        $action     = $request->getActionName();

        $key = $module . "-" . $controller . "-" . $action;

        if (!is_null($user)) {
            $key .= "-".$user->id;
        }

        $this->_uncrypted = $key;

        return md5($key);
    }
}