<?php
class PublicController extends EasyPics_Controller_Action
{
    //http://faceitpages.com/blog/force-an-image-in-facebook-status-update/981/

    //http://stackoverflow.com/questions/5470225/facebook-will-not-display-the-image-from-my-page-as-a-thumbnail-when-posting-it

    public function indexAction()
    {
        $user = EasyPics::getUser();
        $config = EasyPics::getAppConfig();

        $this->_helper->layout->setLayout("public");

        $this->view->user = $user;
        $this->view->Header()->setTitle($config->app->name);
        $this->view->Stylesheet()->setPaths(array(
            "public/css/easypics.css"
        ));
        $this->view->Javascript()->setPaths(array(
            "public/js/libs/head.js",
            "public/js/scripts/easypics.js"
        ));
    }

    public function redirectAction()
    {

        $this->_helper->layout->setLayout("public");

        $album_key = $this->_getParam("album_key", null);
        $picture_key = $this->_getParam("picture_key", null);

        $uaBrowserType = Zend_Registry::get("ua_browser_type");
        $uaFeatures = Zend_Registry::get("ua_features");

        $url = null;
        $redirect = true;
        if ($uaFeatures['is_bot'] || $uaFeatures['is_email'] || $uaFeatures['is_text']) {
            $redirect = false;
        }

        //$redirect = false;

        //Zend_Debug::dump($uaFeatures);
        //Zend_Debug::dump($uaBrowserType);

        //Zend_Debug::dump($album_key);
        //Zend_Debug::dump($picture_key);

        if (!is_null($album_key)) {

            $albums = EasyPics::getModel('albums');
            $album = $albums->getPublicAlbumByKeyUrl($album_key);

            if (!is_null($album)) {

                if ($redirect) {
                    $url = $this->view->baseUrl("pub/#gallery/album/" . $album->id);
                }
                else {

                    $pictures = $album->getPublicPictures();

                    $scriptView = "album";

                    //Zend_Debug::dump($pictures);

                    $this->view->pictures = $pictures;
                }
            }
            else {
                $scriptView = "notfound";
            }
        }
        else if (!is_null($picture_key)) {

            $pictures = EasyPics::getModel('pictures');
            $picture = $pictures->getPublicPictureByKeyUrl($picture_key);

            if (!is_null($picture)) {

                $album = $picture->getAlbum();

                if (!is_null($album) && $album->privacy == 0) {

                    if ($redirect) {
                        $url = $this->view->baseUrl("pub/#gallery/album/" . $album->id."/photo/" . $picture->id);
                    }
                    else {

                        $pictures = $album->getPublicPictures();

                        $scriptView = "picture";

                        $this->view->picture = $picture;
                    }
                }
                else {
                    $scriptView = "notfound";
                }
            }
        }
        else {
            exit("missing params");
        }

        //Zend_Debug::dump($url);
        //exit;

        if ($redirect && !is_null($url)) {

            $url = "http://" . $_SERVER['HTTP_HOST'] . $url;

            $this->_redirect($url);
        }

        $this->render($scriptView);
    }

}