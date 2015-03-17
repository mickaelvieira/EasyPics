<?php
class Api_PictureController extends EasyPics_Rest_Controller
{

    public function indexAction()
    {
        $album_id = $this->_getParam("album_id", null);

        if ($this->_request->isXmlHttpRequest()) {

            $albums = EasyPics::getModel('albums');
            $album = $albums->getAlbum($album_id);

            if (!is_null($album)) {

                $pictures = $album->getPictures();

                $status = true;
                $this->jsonAjaxResponse(true, null, $pictures->toArray());
            }
            else {
                $this->httpCodeResponse(404);
            }
        }
        else {
            $this->httpCodeResponse(403);
        }
    }

    public function getAction()
    {
        $picture_id = $this->_getParam("id", null);

        if ($this->_request->isXmlHttpRequest()) {

            if (!is_null($picture_id)) {

                $pictures = EasyPics::getModel('pictures');
                $picture = $pictures->getPicture($picture_id);

                if (!is_null($picture)) {

                    $this->jsonAjaxResponse(true, null, $picture->toArray());
                }
                else {
                    $this->httpCodeResponse(404);
                }
            }
            else {
                $this->httpCodeResponse(400);
            }
        }
        else {
            $this->httpCodeResponse(403);
        }
    }

    /*public function postAction()
    {
        $rawBody = $this->_request->getRawBody();
        $params = $this->_request->getParams();

        $adapter = new Zend_File_Transfer_Adapter_Http();
        $filesInfos = $adapter->getFileInfo();

        var_dump($this->_request);

        print __METHOD__;

        var_dump($rawBody);
        var_dump($params);
        var_dump($filesInfos);

    }*/

    public function putAction()
    {
        $rawBody = $this->_request->getRawBody();

        if ($this->_request->isXmlHttpRequest() && $rawBody) {

            if ($rawBody) {

                try {
                    $datas = Zend_Json::decode($this->_request->getRawBody());
                }
                catch (Exception $e) {
                    throw new Zend_Exception($e->getMessage());
                }

                $form = EasyPics::getForm('picture');

                if ($form->isValid($datas)) {

                    $pictures = EasyPics::getModel('pictures');
                    $picture = $pictures->updatePicture($datas['id'], $datas);

                    if (!is_null($picture)) {

                        $this->jsonAjaxResponse(true, "Picture have been updated", $picture->toArray());
                    }
                    else {
                        $this->httpCodeResponse(404);
                    }
                }
                else {
                    $this->httpCodeResponse(400);
                }
            }
            else {
                $this->httpCodeResponse(400);
            }
        }
        else {
            $this->httpCodeResponse(403);
        }
    }

    public function deleteAction()
    {

        $picture_id = $this->_getParam("id", null);

        //var_dump($this->getRequest());

        // TODO : vérifier si l'image est la couverture de l'album
        // si oui modifier l'album

        if ($this->_request->isXmlHttpRequest()) {

            if (!is_null($picture_id)) {

                $pictures = EasyPics::getModel('pictures');
                $picture = $pictures->getPicture($picture_id);

                if (!is_null($picture)) {

                    if (is_file(EasyPics::getAppRoot() . $picture->path_to_original)) {
                        @unlink(EasyPics::getAppRoot() . $picture->path_to_original);
                    }

                    // delete cache
                    $cache = EasyPics_Cache_Image::getCache();
                    $cacheDir = $cache->getCacheDir($picture);
                    $cacheKeys = $cache->getAllCacheKeys($picture);

                    // TODO vérifier le bon fonctionnement de cette partie
                    foreach ($cacheKeys as $k => $cacheKey) {

                        // TODO : il faudra gérer les autres extensions de fichiers

                        //var_dump($cacheDir . $cacheKey . ".jpg");
                        //var_dump(is_file($cacheDir . $cacheKey . ".jpg"));

                        if (is_file($cacheDir . $cacheKey . ".jpg")) {
                            @unlink($cacheDir . $cacheKey . ".jpg");
                        }
                    }

                    $pictures->deletePicture($picture_id);

                    $this->jsonAjaxResponse(true, "Picture has been deleted");
                }
                else {
                    $this->httpCodeResponse(404);
                }
            }
            else {
                $this->httpCodeResponse(400);
            }
        }
        else {
            $this->httpCodeResponse(403);
        }
    }
}