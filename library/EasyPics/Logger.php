<?php
class EasyPics_Logger {

    /**
     * Instance de la classe singleton
     * @private Class
     */
    private static $__instance = null;

    /**
     * Autorisation d'instanciation
     * @private Boolean
     */
    private static $allowed = false;



    private static $_logger;

    /**
     * Contructor : not used, singleton class
     * @return Void
     */

    public function __construct()
    {
        self::$_logger = new Zend_Log(
            new Zend_Log_Writer_Stream(
                APPLICATION_PATH . '/../var/logs/system.log'
            )
        );
    }

    public static function getSingleton()
    {
        if (self::$__instance == null) {
            $classname = __CLASS__;
            self::$allowed = true;
            self::$__instance = new $classname;
            self::$allowed = false;
        }
        return self::$__instance;
    }

    public function log($message)
    {
        $config = EasyPics::getAppConfig();
        if (isset($config->logger) && isset($config->logger->enable)) {
            if ($config->logger->enable) {
                self::$_logger->log($message, Zend_Log::NOTICE);
            }
        }
    }

}
