<?php

class Minac_Debug extends Zend_Debug{

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

    /**
     * Contructor : not used, singleton class
     * @return Void
     */
    public function __construct(){
        if (!self::$allowed){
            exit ("Singleton, Instanciation failed - use getInstance function instead of Constructor in ".__CLASS__." line ".__LINE__." ");
        }
    }
    /**
     * Retourne une instance unique
     * @return Class
     */
    public static function getInstance(){

        if (self::$__instance == null){
            $classname = __CLASS__;
            self::$allowed = true;
            self::$__instance = new $classname;
            self::$allowed = false;
        }
        return self::$__instance;
    }

    public function debug($file, $module, $controller, $action, $message = ""){
        print "<div class='debug'>\n";
        print "----------------------------------<br />\n";
        print "File : ".$file."<br />\n";
        print "Module : ".$module."<br />\n";
        print "Controller : ".$controller."<br />\n";
        print "Action : ".$action."<br />\n";
        print $message."<br />\n";
        print "----------------------------------<br />\n";
        print "</div>\n";
    }
}
?>