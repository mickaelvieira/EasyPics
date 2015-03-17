<?php
class EasyPics_Rest_Controller extends Zend_Rest_Controller{


    public function init()
    {
        $this->disableAutoRender();
    }

    public function indexAction()
    {

    }

    public function getAction()
    {

    }

    public function postAction()
    {

    }

    public function putAction()
    {
        $this->jsonAjaxResponse(true, array(), array());
    }

    public function deleteAction()
    {

    }

    protected function disableAutoRender()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
    }

    protected function jsonAjaxResponse($status, $messages = null, $results = null)
    {
        $config = EasyPics::getAppConfig();

        $response = new stdClass();
        $response->status = $status;
        $response->messages = (!is_null($messages)) ? $messages : array();
        $response->results = (!is_null($results)) ? $results : array();
        $json = Zend_Json::encode($response);

        $today = time();
        $maxTime = $config->cache->json->lifetime;
        $serverTime = gmdate("D, d M Y H:i:s", $today) . ' GMT';
        $expireTime = gmdate('D, d M Y H:i:s', $today - 86400) . ' GMT'; // TODO : valeur de durée du cache à mettre dans la config

        $this->_response->setHeader('Content-Type', 'application/json', true)
                        ->setHeader('Cache-Control', 'max-age=' . $maxTime, true)
                        ->setHeader('Cache-Control', 'public', true)
                        ->setHeader('Pragma', '', true)
                        ->setHeader('Date', $serverTime, true)
                        ->setHeader('Expires', $expireTime, true)
                        ->appendBody($json);
    }

    protected function httpCodeResponse($code, $body = "")
    {
        $this->disableAutoRender();
        $this->_response->clearBody();
        $this->_response->clearHeaders();
        $this->_response->setHttpResponseCode($code);
        $this->_response->setBody($body);
    }
}
