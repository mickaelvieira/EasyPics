<?php
class ActionController extends EasyPics_Controller_Action
{

    public function copyAction()
    {
        $album_id     = $this->_getParam("album", null);
        $pictures_ids = $this->_getParam("ids", null);
        $pictures_ids = Zend_Json::decode($pictures_ids);

        if ($this->_request->isXmlHttpRequest() && !is_null($album_id)) {

            $total    = 0;
            $result   = new stdClass();
            $status   = false;
            $messages = array();

            $helper = EasyPics::getHelper("import");
            $albums = EasyPics::getModel("albums");
            $album = $albums->getAlbum($album_id);

            if (!is_null($album)) {

                if (!is_null($pictures_ids) && is_array($pictures_ids)) {

                    foreach ($pictures_ids as $k => $picture_id) {

                        $pictures = EasyPics::getModel("pictures");
                        $picture = $pictures->getPicture($picture_id);

                        if (!is_null($picture)) {

                            $sourcePath = EasyPics::getAppRoot() . $picture->path_to_original;
                            $source = EasyPics::getFile($sourcePath);

                            if ($source instanceof EasyPics_File_Image) {

                                $picture = $helper->addPictureToAlbum($source, $album);
                                $total++;
                            }
                        }
                    }


                    $status = true;
                    $messages[] = $total . " pictures copied";

                    $pictures = $album->getPictures();

                    $result = new stdClass();
                    $result->album = $album->toArray();
                    $result->pictures = $pictures->toArray();
                }
            }
            else {
                $messages[] = "Album doesn't exist";
            }
            $this->jsonAjaxResponse($status, $messages, $result);
        }
    }

    public function moveAction()
    {




    }

    public function importAction()
    {
        // TODO modifler key url par un hash - modifier htaccess ou route
        // TODO modifler key url pour les imahes par un hash.jpg - modifier htaccess ou route

        if ($this->_request->isXmlHttpRequest()) {

            $files = $this->_getParam("files", null);

            $status   = false;
            $result   = array();
            $messages = array();

            $total    = 0;
            $isNew    = false;

            $user   = EasyPics::getUser();
            $helper = EasyPics::getHelper("import");

            if (!is_null($files)) {

                $files = Zend_Json::decode($files, Zend_Json::TYPE_OBJECT);

                if (!empty($files)) {

                    $datas = new stdClass();
                    $datas->album_type = $this->_getParam("import_album_type", null);
                    $datas->album_id   = $this->_getParam("import_album_id", null);
                    $datas->album_name = $this->_getParam("import_album_name", null);

                    $albums = EasyPics::getModel("albums");

                    $deleteFile = false;
                    if (isset($datas->delete_after_import) && $datas->delete_after_import == 1) {
                        $deleteFile = true;
                    }
                    if ($datas->album_type > 0 || $datas->album_id == 0) {
                        $isNew = true;
                    }

                    $album = $this->getAlbum($datas);

                    if (!is_null($album) && $album->id != null) {

                        $temp = EasyPics::getTempDirectory($user);

                        foreach ($files as $iterator => $f) {

                            $file = EasyPics::getFile(EasyPics::getAppRoot() . $f->path);

                            if ($file instanceof EasyPics_File_Archive) {

                                $added = $helper->addArchiveToAlbum($file, $album, $user);
                                $total = $total + $added;

                                $temp->clean();
                            }
                            else if ($file instanceof EasyPics_File_Image) {

                                $picture = $helper->addPictureToAlbum($file, $album, $user);
                                $total++;
                            }

                            if ($deleteFile) {
                                $file->delete();
                            }
                        }

                        $status = true;
                        $messages[] = $total . " pictures Imported";

                        $pictures = $album->getPictures();

                        if ($isNew) {
                            $picture = $pictures->current();
                            $album->cover = $picture->key_url;
                            $album->save();
                        }

                        $result = new stdClass();
                        $result->isNew = $isNew;
                        $result->album = $album->toArray();
                        $result->pictures = $pictures->toArray();

                    }
                    else {
                        $messages[] = "Album doesn't exist";
                    }
                }
                else {
                    $messages[] = "Problem in album json decode";
                }
            }
            $this->jsonAjaxResponse($status, $messages, $result);
        }
    }

    public function folderAction()
    {
        if ($this->_request->isXmlHttpRequest()) {

            $results = array();
            $status = false;
            $message = array();

            $user    = EasyPics::getUser();
            $baseUrl = EasyPics::getBaseUrl();
            $filter  = new EasyPics_Filter_StringUrl();

            $folder = EasyPics::getImportDirectory($user);
            $filesList = $folder->read(array("jpg", "gz", "zip", "tgz"), true);

            foreach ($filesList as $k => $path) {

                $file = EasyPics::getFile($path);

                if ($file instanceof EasyPics_File_Image || $file instanceof EasyPics_File_Archive) {

                    $result = new stdClass();
                    $result->id = $k + 1;
                    $result->ext = $file->getExtension();
                    $result->path = $file->getRelativePath($baseUrl);
                    $result->filename = $file->getFilename();
                    $result->basename = $file->getBasename();
                    $result->key = $filter->filter($file->getBasename()) ."-" . ($k + 1);

                    array_push($results, $result);
                }
            }

            $status	= true;
            $messages[] = "Nombre de fichiers trouvés ".count($results);
            $this->jsonAjaxResponse($status, $messages, $results);
        }
    }

    public function uploadAction()
    {

        $result   = new stdClass();
        $status   = false;
        $messages = array();

        $total    = 0;
        $isNew    = false;

        $user   = EasyPics::getUser();
        $helper = EasyPics::getHelper("import");
        $temp   = EasyPics::getTempDirectory($user);

        $adapter = new Zend_File_Transfer_Adapter_Http();
        $adapter->setDestination($temp->getPath());
        $adapter->addValidator('Size', false, array('min' => 1000, 'max' => 10000000)); // 1000, // 1ko'max' => 2000000 // 2Mo
        $adapter->addValidator('Extension', false, array("jpg", "gz", "zip", "tgz"));

        $filesInfos = $adapter->getFileInfo();

        $isValid = true;
        foreach ($filesInfos as $field_name => $info) {
            if (!$adapter->isValid($field_name)) {
                $isValid = false;
                break;
            }
        }

        if ($isValid) {

            $isUploaded = true;
            foreach ($filesInfos as $field_name => $info) {
                if (!$adapter->isUploaded($field_name)) {
                    $isUploaded = false;
                    break;
                }
            }

            if ($isUploaded) {

                if ($adapter->receive()) {

                    $datas = new stdClass();
                    $datas->album_type = $this->_getParam("upload_album_type", null);
                    $datas->album_id   = $this->_getParam("upload_album_id", null);
                    $datas->album_name = $this->_getParam("upload_album_name", null);

                    $album = $this->getAlbum($datas);

                    if ($datas->album_type == 1 || $datas->album_id == 0) {
                        $isNew = true;
                    }

                    if (!is_null($album) && $album->id != null) {

                        foreach ($filesInfos as $field_name => $info) {

                            $filesNames = $adapter->getFileName($field_name);
                            $file = EasyPics_File::factory($filesNames);

                            if ($file instanceof EasyPics_File_Archive) {

                                $added = $helper->addArchiveToAlbum($file, $album, $user);
                                $total = $total + $added;
                            }
                            else if ($file instanceof EasyPics_File_Image) {

                                $picture = $helper->addPictureToAlbum($file, $album, $user);
                                $total++;
                            }
                        }

                        $temp->clean();

                        $status = true;
                        $messages[] = $total . " pictures Uploaded";

                        $pictures = $album->getPictures();

                        if ($isNew) {
                            $picture = $pictures->current();
                            $album->cover = $picture->key_url;
                            $album->save();
                        }

                        $result = new stdClass();
                        $result->isNew = $isNew;
                        $result->album = $album->toArray();
                        $result->pictures = $pictures->toArray();
                    }
                    else {
                        $messages[] = "Album not found !!!";
                    }
                }
                else {
                    $messages[] = "Fichiers non reçus !!!";
                }
            }
            else {
                $messages[] = "Fichiers non envoyés !!!";
            }
        }
        else {
            $messages[] = $adapter->getMessages();
            $messages[] = "Fichiers invalides !!!";
        }
        $this->jsonAjaxResponse($status, $messages, $result);
    }

    protected function getAlbum($datas)
    {
        $album  = null;
        $albums = EasyPics::getModel("albums");

        if ($datas->album_type == 0 && $datas->album_id != 0) {
            $album = $albums->getAlbum($datas->album_id);
        }
        else {
            $album = $albums->addAlbum(array(
                'name' => $datas->album_name
            ));
        }
        return $album;
    }

}