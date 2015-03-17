<?php
/**
 * 
 */
class Cron_IndexController extends EasyPics_Controller_Action
{

    public function init()
    {
        $this->disableAutoRender();
    }

    public function indexAction()
    {



    }

    public function importAction()
    {
        $helper  = EasyPics::getHelper("import");
        $albums  = EasyPics::getModel("albums");
        $users   = EasyPics::getModel("users");
        $members = $users->getUsers();

        EasyPics::log("-- Start cron import process ".date("Y-m-d H:i:s")." --");

        foreach ($members as $member) {

            $total = 0;
            $albums->setUser($member);

            try {

                $temp   = EasyPics::getTempDirectory($member);
                $folder = EasyPics::getCronDirectory($member);
                $files  = $folder->read(array("gz", "zip", "tgz"));

                EasyPics::log(count($files) . " archive files found...");

                foreach ($files as $path) {

                    $file = EasyPics::getFile($path);

                    if ($file instanceof EasyPics_File_Archive) {

                        EasyPics::log("Import file " . $file->getPath());

                        $name = $file->getFilename();

                        $album = $albums->addAlbum(array(
                            'name' => $name
                        ));

                        if (!is_null($album)) {

                            EasyPics::log("Create album [".$album->name.":".$album->id."]");

                            $added = 0;
                            $added = $helper->addArchiveToAlbum($file, $album, $member);

                            $total = $total + $added;

                            $pictures = $album->getPictures();
                            $picture = $pictures->current();
                            $album->cover = $picture->key_url;
                            $album->save();

                            $temp->clean();
                            $file->delete();

                            EasyPics::log($added . " files added");
                        }
                    }
                }
            }
            catch (Zend_Exception $e) {

                EasyPics::log($e);
                continue;
            }
        }

        EasyPics::log($total . " files imported");
        EasyPics::log("-- End cron import process - ".date("Y-m-d H:i:s")." --");

        exit;
    }

    public function cacheAction()
    {
        $total   = 0;
        $cache   = EasyPics_Cache_Image::getCache();
        $helper  = EasyPics::getHelper("image");
        $albums  = EasyPics::getModel("albums");
        $users   = EasyPics::getModel("users");
        $members = $users->getUsers();
        $continue = true;

        EasyPics::log("-- Start cron cache process ".date("Y-m-d H:i:s")." --");

        try {
            foreach ($members as $member) {

                $total = 0;
                $albums->setUser($member);

                $userAlbums = $albums->getAlbums();

                foreach ($userAlbums as $album) {

                    $pictures = $album->getPictures();

                    foreach ($pictures as $picture) {

                        $cacheDir  = $cache->getCacheDir($picture);
                        $cacheKeys = $cache->getAllCacheKeys($picture);

                        foreach ($cacheKeys as $type => $cacheKey) {

                            $imageConfig = $helper->getImageConfig($type);

                            if (!is_file($cacheDir . $cacheKey . ".jpg")) {
                                $pathCache = $cache->load($picture, $imageConfig);

                                if ($pathCache) {
                                    $total++;

                                    if ($total >= 10) {
                                        $continue = false;
                                    }
                                }
                            }

                            if (!$continue) {
                                break;
                            }

                        }

                        if (!$continue) {
                            break;
                        }

                    }

                    if (!$continue) {
                        break;
                    }
                }

                if (!$continue) {
                    break;
                }

            }
        }
        catch (exception $e) {
            EasyPics::log($e);
        }


        EasyPics::log($total . " files built");
        EasyPics::log("-- End cron cache process - ".date("Y-m-d H:i:s")." --");

        exit;
    }
}

