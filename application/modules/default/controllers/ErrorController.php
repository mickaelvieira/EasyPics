<?php
class ErrorController extends EasyPics_Controller_Action
{

    public function errorAction()
    {

        // http://framework.zend.com/manual/fr/zend.controller.plugins.html
        $this->_helper->layout->setLayout("error");
        $this->view->Stylesheet()->setPaths(array(
            "public/css/easypics.css"
        ));

        $this->getResponse()->clearBody();

        $errors = $this->_getParam('error_handler');
        if (!$errors || !$errors instanceof ArrayObject) {
            $this->view->message = 'You have reached the error page';
            return;
        }


        //Zend_Debug::dump(realpath(APPLICATION_PATH . '/../logs/application.log'));
        //exit;

        $log = new Zend_Log(
            new Zend_Log_Writer_Stream(
                APPLICATION_PATH . '/../var/logs/application.log'
            )
        );          

        $message = "";
        $messages = array();

        switch ($errors->type) {

            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ROUTE:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:

                $scriptView = '404';
                $message = 'Page not found';
                $priority = Zend_Log::NOTICE;
                $codeHTTP = 404;

                //$this->getResponse()->setHttpResponseCode(404);

                break;
            default:

                $scriptView = 'error';
                $message = 'Application error';
                $priority = Zend_Log::CRIT;
                $codeHTTP = 500;
              //  $this->getResponse()->setHttpResponseCode(500);
                break;
        }

        $log->log('Error : ' . $message, $priority);
        $log->log('Request Parameters : ', $priority);
        foreach ($errors->request->getParams() as $k => $v) {
            $log->log($k . ' : ' .$v, $priority);
            if (APPLICATION_ENV == "development" || APPLICATION_ENV == "preproduction") {
                $message .= "<br>" . $k . " : " . $v;
            }
            
        }
        if (isset($errors->exception) && ($errors->exception instanceof Exception)) {
            $log->log('Error message : ' . $errors->exception->getMessage(), $priority);
            $log->log('Error trace : '."\n" . $errors->exception->getTraceAsString(), $priority);
            
            if (APPLICATION_ENV == "development" || APPLICATION_ENV == "preproduction") {
                $message .= "<br>" . $errors->exception->getMessage();
                $message .= "<br>" . nl2br($errors->exception->getTraceAsString());
            }            
        }

        if ($this->_request->isXmlHttpRequest()) {
            $this->httpCodeResponse($codeHTTP, $message);
        }
        else {
            $this->getResponse()->setHttpResponseCode($codeHTTP);
            $this->view->message = $message;
            $this->render($scriptView);
        }
    }

    public function aclAction()
    {
        $this->_helper->layout->setLayout("error");
        $this->view->Stylesheet()->setPaths(array(
            "public/css/easypics.css"
        ));

        $this->getResponse()->clearBody();
        $this->getResponse()->clearHeaders();
        $this->getResponse()->setHttpResponseCode(401);

        $message = "vous n'avez pas les droits nécessaires";
        if ($this->_request->isXmlHttpRequest()) {
            $this->jsonAjaxResponse(false, $message);
        }
        else {
            // normalement ce cas ne devrait pas produire voir EasyPics_Plugin_Auth
            $this->view->message = "<ul><li>" . $message . "</li></ul>";
        }
    }

}
?>
