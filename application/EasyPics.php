<?php
final class EasyPics
{


    static private $_baseUrl;



    static private $_registry;



    static private $_appConfig;


    static private $_root;

    public static function register($key, $value)
    {
        if (isset(self::$_registry[$key])) {
            throw new Zend_Exception('Registry key "'.$key.'" already exists');
        }
        self::$_registry[$key] = $value;
    }

    /**
    * Unregister a variable from register by key
    *
    * @param string $key
    */
    public static function unregister($key)
    {
        if (isset(self::$_registry[$key])) {
            if (is_object(self::$_registry[$key]) && (method_exists(self::$_registry[$key], '__destruct'))) {
                self::$_registry[$key]->__destruct();
            }
            unset(self::$_registry[$key]);
        }
    }

    /**
    * Retrieve a value from registry by a key
    *
    * @param string $key
    * @return mixed
    */
    public static function registry($key)
    {
        if (isset(self::$_registry[$key])) {
            return self::$_registry[$key];
        }
        return null;
    }


    static public function getUser()
    {
        if (Zend_Session::isStarted()) {
            $auth = Zend_Auth::getInstance();
            if ($auth->hasIdentity()) {
                return $auth->getStorage()->read();
            }
        }
        return null;
    }


    static public function getAppConfig()
    {
        if (is_null(self::$_appConfig)) {
            self::$_appConfig = self::getConfig("config");
        }
        return self::$_appConfig;
    }

    static public function getConfig($name)
    {
        return (Zend_Registry::isRegistered($name)) ? Zend_Registry::get($name) : null;
    }

    static public function getBaseUrl()
    {
        if (is_null(self::$_baseUrl)) {

            $front   = Zend_Controller_Front::getInstance();
            $baseUrl = $front->getBaseUrl();

            if (!preg_match("/\/$/", $baseUrl)) {
                $baseUrl .= "/";
            }
            self::$_baseUrl = $baseUrl;
        }

        return self::$_baseUrl;
    }

    static function getAppRoot()
    {
        if (is_null(self::$_root)) {
            self::$_root = realpath(APPLICATION_PATH . "/../") . "/";
            self::$_root = preg_replace("/".preg_quote(DIRECTORY_SEPARATOR, "/")."/", "/", self::$_root);

        }
        return self::$_root;
    }

    static public function getCachePath()
    {
        return self::getAppRoot() . "var/cache/";
    }

    static public function getImageCachePath()
    {
        return self::getCachePath() . "images/";
    }

    static public function getHTMLCachePath()
    {
        return self::getCachePath() . "html/";
    }

    static public function getLogsPath()
    {
        return self::getAppRoot() . "var/logs/";
    }

    static public function getTempPath($user = null)
    {
        if (!is_null($user)) {
            return self::getUsersPath() . $user->username . "/temp/";
        }
        else {
            return self::getAppRoot() . "var/temp/";
        }
    }

    static public function getTempDirectory($user = null)
    {
        return EasyPics_File::factory(self::getTempPath($user));
    }

    static public function getUsersPath()
    {
        return self::getAppRoot() . "users/";
    }

    static function getImportPath($user)
    {
        return self::getUsersPath() . $user->username . "/import/";
    }

    static function getImportDirectory($user)
    {
        return EasyPics_File::factory(self::getImportPath($user));
    }

    static function getCronPath($user)
    {
        return self::getUsersPath() . $user->username . "/cron/";
    }

    static function getCronDirectory($user)
    {
        return EasyPics_File::factory(self::getCronPath($user));
    }

    static public function getAlbumsPath($user, $album_id = null)
    {
        $path =	self::getUsersPath() . $user->username . "/albums/";
        if (!is_null($album_id)) {
            $path .= $album_id . "/";
        }
        return $path;
    }

    static public function getAlbumsDirectory($user, $album_id = null)
    {
        return EasyPics_File::factory(self::getAlbumsPath($user, $album_id));
    }

    static public function getFile($path)
    {
        return EasyPics_File::factory($path);
    }

    static public function getModel($modelName)
    {

        $prefixe     = "Application_Model_DbTable_";
        $registryKey = "_db_table_" . $modelName;

        if (!self::registry($registryKey)) {
            self::register($registryKey, self::getInstance($prefixe, $modelName));
        }

        return self::registry($registryKey);
    }

    static public function getHelper($helperName)
    {
        $prefixe     = "EasyPics_Controller_Action_Helper_";
        $registryKey = "_action_helper_" . $helperName;

        if (!self::registry($registryKey)) {
            self::register($registryKey, self::getInstance($prefixe, $helperName));
        }

        return self::registry($registryKey);
    }

    static public function getForm($formName)
    {
        $prefixe     = "Application_Form_";
        $registryKey = "_form_" . $formName;

        if (!self::registry($registryKey)) {
            self::register($registryKey, self::getInstance($prefixe, $formName));
        }

        return self::registry($registryKey);
    }

    static public function getInstance($prefixe, $identifier)
    {
        $classname  = $prefixe . ucfirst(mb_convert_case($identifier, MB_CASE_LOWER));

        if (class_exists($classname)) {
            $reflection = new ReflectionClass($classname);
            if ($reflection->IsInstantiable()) {
                return $reflection->newInstance();
            }
        }
        throw new Zend_Exception("Cannot instanciate class '" . $classname . "' ");
    }

    static public function log($message)
    {
        $config = self::getAppConfig();
        if (isset($config->logger) && isset($config->logger->enable)) {
            EasyPics_Logger::getSingleton()->log($message);
        }
    }

    // TODO : doit être déplacer dans la future création de compte
    static public function getSalt()
    {
        return sha1("googlepicasawebkiller");
    }

    static public function run()
    {
        $application = new Zend_Application(
            APPLICATION_ENV,
            realpath(APPLICATION_PATH . '/conf/settings.ini')
        );
        $application->bootstrap()->run();
    }

    static function cron($params)
    {
        $application = new Zend_Application(
            APPLICATION_ENV,
            realpath(APPLICATION_PATH . '/conf/settings.ini')
        );

        $params     = array_reverse(explode('/', $params));
        $module     = array_pop($params);
        $controller = array_pop($params);
        $action     = array_pop($params);

        $request = new Zend_Controller_Request_Simple($action, $controller, $module);

        $front = $application->getBootstrap()->bootstrap('frontController')->getResource('frontController');
        $front->setRequest($request)
               ->setResponse(new Zend_Controller_Response_Cli())
               ->setRouter(new EasyPics_Controller_Router_Cli())
               ->throwExceptions(true);

        $application->bootstrap()->run();
    }
}
