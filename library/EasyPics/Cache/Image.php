<?php
class EasyPics_Cache_Image
{

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
     * Cache Directory
     * @private Object
     */
    private static $_dir;

    /**
     * Contructor : not used, singleton class
     * @return Void
     */
    public function __construct()
    {	
        if (!self::$allowed) {
            exit ("Singleton, Instanciation failed - use getCache function instead of Constructor in ".__CLASS__." line ".__LINE__." ");
        }

        self::$_dir = EasyPics::getImageCachePath();
    }

    /**
     * Retourne une instance unique
     * @return Class
     */
    static public function getCache()
    {
        if (self::$__instance == null) {
            $classname = __CLASS__;
            self::$allowed = true;
            self::$__instance = new $classname();
            self::$allowed = false;
        }
        return self::$__instance;
    }

    static public function load(Application_Model_Row_Picture $source, $imgConfig)
    {
        $image = EasyPics::getFile($source->path_to_original);

        if (!($image instanceof EasyPics_File_Image)) {
            throw new Zend_Exception("Image is not an instance of EasyPics_File_Image");
        }

        if (!isset($imgConfig) ||
            !isset($imgConfig->width) ||
            !isset($imgConfig->height) ||
            !isset($imgConfig->type) ||
            !isset($imgConfig->compression)) {

            throw new Zend_Exception("Invalid image configuration");
        }

        $width       = $imgConfig->width;
        $height      = $imgConfig->height;
        $type        = $imgConfig->type;
        $compression = $imgConfig->compression;

        $cacheKey = self::getCacheKey($source->path_to_original, $width, $height, $type, $compression);
        $cacheDir = self::getCacheDir($source);
        $cachePath = $cacheDir . $cacheKey . "." . $image->getExtension();

        $build = false;
        $tsSource = filemtime($image->getPath());
        if (is_file($cachePath)) {
            $tsDestination = filemtime($cachePath);
            if ($tsSource > $tsDestination) {
                $build = true;
            }
        }
        else {
            $build = true;
        }

        if ($build) {	

            EasyPics_Logger::getSingleton()->log("Build cache ".$cachePath);

            if ($type == "crop") {
                $image->resizeAndCrop($cachePath, $width, $height, $compression);
            }
            else if ($type == "resize") {
                $image->resize($cachePath, $width, $height);
            }
            else {
                throw new Zend_Exception("Invalid cache type");
            }
        }
        else {
            EasyPics_Logger::getSingleton()->log("Get From Cache ".$cachePath);
        }
        return $cachePath;
    }
    
    static public function getCacheDir(Application_Model_Row_Picture $source)
    {
        $dir = self::$_dir;

        if (!preg_match("/\/$/", $dir)) {
            $dir .= "/";
        }

        if (isset($source->album) && !is_null($source->album) && !empty($source->album)) {

            $dir .= $source->album . "/";

            if (!is_dir($dir)) {
                try {
                    mkdir($dir, 0755);
                }
                catch (Exception $e) {
                    throw new Zend_Exception($e->getMessage());
                }
            }
        }
        return $dir;
    }

    static public function getCacheKey($path, $width, $height, $type, $compression)
    {
        return md5($path . "x" . $width . "x" . $height . "x" . $type . "x" . $compression);
    }

    static public function getAllCacheKeys(Application_Model_Row_Picture $source)
    {
        $config = EasyPics::getAppConfig();

        $cacheKeys = array();
        if (isset($config->easypics->imgSettings)) {
            foreach ($config->easypics->imgSettings as $type => $conf) {
                if (isset($conf->width) && isset($conf->height) && isset($conf->type) && isset($conf->compression)) {
                    $cacheKey = self::getCacheKey($source->path_to_original, $conf->width, $conf->height, $conf->type, $conf->compression);

                    $cacheKeys[$type] = $cacheKey;

                    //array_push($cacheKeys, $cacheKey);
                }
            }
        }
        return $cacheKeys;
    }
}