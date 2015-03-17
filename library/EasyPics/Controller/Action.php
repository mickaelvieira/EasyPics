<?php
class EasyPics_Controller_Action extends Zend_Controller_Action
{

    public function  __construct(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response, array $invokeArgs = array())
    {    
        parent::__construct($request, $response, $invokeArgs);
    }

    protected function jsonAjaxResponse($status, $messages = null, $results = null)
    {
        $this->disableAutoRender();

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
                        ->setBody($json);
    }
    
    protected function imageResponse($bits, $mimetype, $lastModified)
    {
        $this->disableAutoRender();

        $today = time();
        $maxTime = 3600;
        $serverTime = gmdate("D, d M Y H:i:s", $today) . ' GMT';
        $expireTime = gmdate('D, d M Y H:i:s', $today + $maxTime) . ' GMT';
        $lastModified = gmdate("D, d M Y H:i:s", $lastModified) . ' GMT';

        $this->_response->setHeader('Content-Type', $mimetype, true)
                        ->setHeader('Cache-Control', 'max-age=' . $maxTime, true)
                        ->setHeader('Cache-Control', 'public', true)
                        ->setHeader('Pragma', '', true)
                        ->setHeader('Date', $serverTime, true)
                        ->setHeader('Expires', $expireTime, true)
                        ->setHeader('Last-Modified', $lastModified, true)
                        ->setBody($bits);
    }
    
    protected function httpCodeResponse($code, $body = "")
    {
        $this->disableAutoRender();
        $this->_response->clearBody();
        $this->_response->clearHeaders();
        $this->_response->setHttpResponseCode($code);
        $this->_response->setBody($body);
    }

    protected function disableAutoRender()
    {	
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
    }
}