<?php
class ImageController extends EasyPics_Controller_Action
{

    protected $_imageType = array();

    public function indexAction()
    {
        $user   = EasyPics::getUser();
        $helper = EasyPics::getHelper("image");

        $key  = $this->_getParam('key', null);
        $type = $this->_getParam('type', null);

        if (!is_null($key) && !is_null($type)) {

            $imageConfig = $helper->getImageConfig($type);

            if (!is_null($imageConfig)) {

                $pictures = EasyPics::getModel("pictures");

                if (is_null($user)) {
                    $picture = $pictures->getPublicPictureByKeyUrl($key);
                }
                else {
                    $picture = $pictures->getPictureByKeyUrl($key);
                }

                if (!is_null($picture)) {

                    $cache     = EasyPics_Cache_Image::getCache();
                    $pathCache = $cache->load($picture, $imageConfig);

                    $today    = time();
                    $fileTime = filemtime($pathCache);

                    if ($this->_request->getHeader('If-Modified-Since')) {
                        $ifModifiedSince = @strtotime($this->_request->getHeader('If-Modified-Since'));
                        if ($ifModifiedSince >= $fileTime) {

                            EasyPics::log("Not modified ".$this->_request->getHeader('If-Modified-Since'));

                            $this->_response->setHttpResponseCode(304)->setHeader('Connection', 'close', true);
                        }
                    }
                    $bits = @file_get_contents($pathCache);
                    $this->imageResponse($bits, $picture->mimetype, $fileTime);
                }
                else {
                    $this->httpCodeResponse(404, "Requested picture ".$key." not found");
                }
            }
            else {
                $this->httpCodeResponse(404, "Invalid request");
            }
        }
        else {
            $this->httpCodeResponse(404, "Invalid request");
        }
    }

}