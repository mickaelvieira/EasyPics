<?php
class EasyPics_Plugin_Config extends Zend_Controller_Plugin_Abstract
{

    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {

        $htmlCacheDir  = EasyPics::getHTMLCachePath();
        $imageCacheDir = EasyPics::getImageCachePath();
        $logsDir       = EasyPics::getLogsPath();
        $tempDir       = EasyPics::getTempPath();
        $usersDir      = EasyPics::getUsersPath();

        if (!is_dir($htmlCacheDir)) {
            mkdir($htmlCacheDir, 0755, true);
        }
        if (!is_dir($imageCacheDir)) {
            mkdir($imageCacheDir, 0755, true);
        }
        if (!is_dir($logsDir)) {
            mkdir($logsDir, 0755, true);
        }
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }
        if (!is_dir($usersDir)) {
            mkdir($usersDir, 0755, true);
        }

        if (Zend_Session::isStarted()) {

            $auth = Zend_Auth::getInstance();

            if ($auth->hasIdentity()) {

                $user = EasyPics::getUser();

                if (!is_null($user)) {

                    $usersBaseDir = EasyPics::getUsersPath() . $user->username . "/";
                    $importDir    = EasyPics::getImportPath($user);
                    $albumsDir    = EasyPics::getAlbumsPath($user);
                    $cronDir      = EasyPics::getCronPath($user);
                    $tempDir      = EasyPics::getTempPath($user);

                    if (!is_dir($usersBaseDir)) {
                        mkdir($usersBaseDir, 0755, true);
                    }
                    if (!is_dir($importDir)) {
                        mkdir($importDir, 0755, true);
                    }
                    if (!is_dir($albumsDir)) {
                        mkdir($albumsDir, 0755, true);
                    }
                    if (!is_dir($cronDir)) {
                        mkdir($cronDir, 0755, true);
                    }
                    if (!is_dir($tempDir)) {
                        mkdir($tempDir, 0755, true);
                    }
                }
            }
        }
    }
}