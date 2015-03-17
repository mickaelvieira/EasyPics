<?php
class Api_AlbumController extends EasyPics_Rest_Controller
{

    public function indexAction()
    {

        if ($this->_request->isXmlHttpRequest()) {

            $user = EasyPics::getUser();

            $albums = EasyPics::getModel('albums');
            $userAlbums = $albums->getAlbums();

            $results = array();
            foreach ($userAlbums as $k =>  $album) {

                $pictures = $album->getPictures();
                $result = $album->toArray();

                $result['total_pictures'] = $pictures->count();
                $result['username'] = $user->firstname." ".$user->lastname;

                array_push($results, $result);
            }
            $this->jsonAjaxResponse(true, null, $results);
        }
        else {
            $this->httpCodeResponse(403);
        }
    }

    public function getAction()
    {
        $album_id = $this->_getParam("id", null);

        if ($this->_request->isXmlHttpRequest()){

            if (!is_null($album_id)) {

                $user = EasyPics::getUser();

                $albums = EasyPics::getModel('albums');
                $album = $albums->getAlbum($album_id);

                if (!is_null($album)) {

                    $pictures = $album->getPictures();

                    $result = $album->toArray();
                    $result['username'] = $user->firstname." ".$user->lastname;
                    $result['total_pictures'] = $pictures->count();

                    $this->jsonAjaxResponse(true, null, $result);
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

    public function postAction()
    {
        $rawBody = $this->_request->getRawBody();

        if ($this->_request->isXmlHttpRequest()) {

            if ($rawBody) {

                $user = EasyPics::getUser();
                $datas = Zend_Json::decode($rawBody);

                $albums = EasyPics::getModel('albums');
                $album = $albums->addAlbum($datas);

                $result = $album->toArray();
                $result['username'] = $user->firstname." ".$user->lastname;
                $result['total_pictures'] = 0;

                $this->jsonAjaxResponse(true, "Album has been created", $result);
            }
            else {
                $this->httpCodeResponse(400);
            }
        }
        else {
            $this->httpCodeResponse(403);
        }
    }

    public function putAction()
    {
        $rawBody = $this->_request->getRawBody();

        if ($this->_request->isXmlHttpRequest()) {

            if ($rawBody) {

                try {
                    $datas = Zend_Json::decode($this->_request->getRawBody());
                }
                catch (Exception $e) {
                    throw new Zend_Exception($e->getMessage());
                }

                $form = EasyPics::getForm('album');

                if ($form->isValid($datas)) {

                    $albums = EasyPics::getModel('albums');
                    $album = $albums->updateAlbum($datas['id'], $datas);

                    if (!is_null($album)) {

                        $album->setPicturesPrivacy($datas['privacy']);
                        $this->jsonAjaxResponse(true, "Album has been updated", $album->toArray());
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
        $album_id = $this->_getParam("id", null);

        if ($this->_request->isXmlHttpRequest()) {

            if (!is_null($album_id)) {

                $user   = EasyPics::getUser();
                $albums = EasyPics::getModel('albums');
                $album  = $albums->getAlbum($album_id);

                if (!is_null($album)) {

                    $pathAlbum = EasyPics::getAlbumsPath($user, $album->id);

                    if (is_dir($pathAlbum)) {
                        $albumDirectory = EasyPics::getAlbumsDirectory($user, $album->id);
                        $albumDirectory->clean();
                        @rmdir($pathAlbum);
                    }

                    $pathCache = EasyPics::getImageCachePath() . $album->id . "/";

                    if (is_dir($pathCache)) {
                        $cacheDirectory = EasyPics_File::factory($pathCache);
                        $cacheDirectory->clean();
                        @rmdir($pathCache);
                    }

                    $albums->deleteAlbum($album_id);

                    $this->jsonAjaxResponse(true, "Album has been deleted");
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
