<?php
class Gallery_PictureController extends EasyPics_Rest_Controller
{

    public function indexAction()
    {
        $album_id = $this->_getParam("album_id", null);

        if ($this->_request->isXmlHttpRequest()) {

            if (!is_null($album_id)) {

                $albums = EasyPics::getModel('albums');
                $album = $albums->getPublicAlbum($album_id);

                if (!is_null($album)) {

                    $pictures = $album->getPublicPictures();
                    $this->jsonAjaxResponse(true, null, $pictures);
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
