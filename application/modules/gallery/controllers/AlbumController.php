<?php
class Gallery_AlbumController extends EasyPics_Rest_Controller
{

    public function indexAction()
    {

    }

    public function getAction()
    {
        $album_id = $this->_getParam("id", null);

        if ($this->_request->isXmlHttpRequest()) {

            if (!is_null($album_id)) {

                $albums = EasyPics::getModel('albums');
                $album = $albums->getPublicAlbum($album_id);

                if (!is_null($album)) {

                    $user = $album->getUser();
                    $album = $album->filteredDatas();

                    $album['username'] = $user->firstname." ".$user->lastname;

                    $this->jsonAjaxResponse(true, null, $album);
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
