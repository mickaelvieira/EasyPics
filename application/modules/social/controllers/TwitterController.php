<?php
class Social_TwitterController extends EasyPics_Controller_Action
{



    public function indexAction()
    {
        $user = EasyPics::getUser();
        $config = EasyPics::getAppConfig();
        // Default namespace
        $session = new Zend_Session_Namespace();

        try {

            // Check if user have access token.
            if (empty($user->twitter_token)) {

                $this->_helper->redirector->gotoSimple("authenticate", "twitter", "social");

                //throw new Exception( 'You are not logged in. Please, try again.' );
            }

            // Unserialize access token
            $accessToken = unserialize($user->twitter_token);

            //var_dump($accessToken);

           // $zendConfig = Zend_Registry::get( 'Zend_Config' );

            // Prepare a config array with access token and config options
            $config = $config->twitter->toArray();
            $config['username'] = $accessToken->getParam('screen_name');
            $config['accessToken'] = $accessToken;

            $twitter = new Zend_Service_Twitter($config);
            $response = $twitter->account->verifyCredentials(); // Verify if credentials work

            //Zend_Debug::dump($response);

            if (!$response || !empty($response->error) ) {
                throw new Exception( 'Wrong credentials. Please, try to login again.' );
            }



            $twitterUser = new stdClass();
            $twitterUser->screen_name = $response->screen_name;
            $twitterUser->profile_image_url = $response->profile_image_url;



            var_dump($response);
            var_dump($twitterUser);
            //Zend_Debug::dump($twitter->user->show("screen_name"));

           // Zend_Debug::dump($twitter->status->userTimeLine());

            exit;

        }
        catch ( Exception $e ) {

            echo $e->getMessage();
        }

        //die();

    }

    public function getAction()
    {
        $user = EasyPics::getUser();
        $config = EasyPics::getAppConfig();

        if ($this->_request->isXmlHttpRequest()) {

            // Default namespace
            //$session = new Zend_Session_Namespace();

            $result = new stdClass();
            $status = false;
            $messages = array();

            if (!empty($user->twitter_token)) {

                $accessToken = unserialize($user->twitter_token);

                $config = $config->twitter->toArray();
                $config['username'] = $accessToken->getParam('screen_name');
                $config['accessToken'] = $accessToken;

                $twitter = new Zend_Service_Twitter($config);
                $response = $twitter->account->verifyCredentials(); // Verify if credentials work

                if ($response && empty($response->error) ) {

                    //var_dump($response);

                    $status = true;
                    $result = new stdClass();
                    $result->screen_name = strval($response->screen_name);
                    $result->profile_image_url = strval($response->profile_image_url);
                    $result->profil_url = "http://twitter.com/" . $result->screen_name;

                    //var_dump($result);

                }
                else {
                    $status = false;
                    $messages[] = $response->error;
                }
            }
            else {
                $status = false;
                $messages[] = "user non authenticate !!!";
            }

            $this->jsonAjaxResponse($status, $messages, $result);
        }

    }

    public function authenticateAction()
    {
        $config = EasyPics::getAppConfig();
        $session = new Zend_Session_Namespace();

        $front = Zend_Controller_Front::getInstance();
        $baseUrl = $front->getBaseUrl();

        if (!preg_match("/\/$/", $baseUrl)) {
            $baseUrl .= "/";
        }

        $config = $config->twitter->toArray();
        $config['callbackUrl'] = "http://" . $_SERVER['HTTP_HOST'] . $baseUrl . "social/twitter/callback";


        //Zend_Debug::dump($config);
        //exit;
        $consumer = new Zend_Oauth_Consumer($config);

        //Zend_Debug::dump($consumer);
        //exit;

        $requestToken = $consumer->getRequestToken();

        //Zend_Debug::dump($requestToken);
        //exit;

        $session->requestToken = serialize($requestToken);

        //redirect user to twitter
        $consumer->redirect();
    }

    public function callbackAction()
    {
        $user = EasyPics::getUser();
        $config = EasyPics::getAppConfig();
        $session = new Zend_Session_Namespace();
        $requestToken = $session->requestToken;

        $consumer = new Zend_Oauth_Consumer($config->twitter->toArray());
        $qstring = $this->_request->getQuery();

        if (!empty($qstring) && !empty($requestToken)) {
 
            // Get access token
            $accessToken = $consumer->getAccessToken($qstring, unserialize($requestToken));

            // load user
            $users = new Application_Model_DbTable_Users();
            $select = $users->select();
            $select->where('id = ?', $user->id);
            $userRow = $users->fetchRow($select);

            if (!is_null($user)) {

                //$user->twitter_token = serialize($accessToken);
                $userRow->twitter_token = serialize($accessToken);
                $userRow->date_modified = new Zend_Db_Expr('NOW()');
                $userRow->save();

                //exit;
                $this->_helper->redirector->gotoSimple("index", "twitter", "social");
            }
            else {
                throw new Zend_Exception('User doesn\t exist');
            }
        }
        else {
            throw new Zend_Exception( 'Invalid access. No token provided.' );
        }
    }

    public function updateAction()
    {
        $user = EasyPics::getUser();
        $config = EasyPics::getAppConfig();
        $message = $this->_getParam("tweet", null);

        if ($this->_request->isXmlHttpRequest()) {

            // Default namespace
            $session = new Zend_Session_Namespace();

            $status = false;
            $messages = array();

            if (!is_null($message)) {

                if (!empty($user->twitter_token)) {

                    $accessToken = unserialize($user->twitter_token);

                    $config = $config->twitter->toArray();
                    $config['username'] = $accessToken->getParam('screen_name');
                    $config['accessToken'] = $accessToken;

                    $twitter = new Zend_Service_Twitter($config);
                    $response = $twitter->account->verifyCredentials(); // Verify if credentials work

                    if ($response && empty($response->error) ) {

                        try {
                            $response = $twitter->status->update($message);

                            if ($response && empty($response->error) ) {
                                $status = true;
                                $messages[] = "Your tweet has been sent";
                            }
                            else {
                                $status = false;
                                $messages[] = $response->error;
                            }
                        }
                        catch (Zend_Service_Twitter_Exception $e) {
                            $status = false;
                            $messages[] = $e->getMessage();
                        }
                    }
                    else {
                        $status = false;
                        $messages[] = $response->error;
                    }
                }
                else {
                    $status = false;
                    $messages[] = "user non authenticate !!!";
                }
            }
            else {
                $status = false;
                $messages[] = "Tweet is empty";
            }

            $this->jsonAjaxResponse($status, $messages);
        }

    }

    public function checkAction()
    {

        if ($this->_request->isXmlHttpRequest()) {

            // Default namespace
            $session = new Zend_Session_Namespace();

            $status = false;
            $messages = array();

            if ($this->_isAuthenticate) {
                $status = true;
                $messages[] = "user authenticate !!!";
            }
            else {
                $status = false;
                $messages[] = "user non authenticate !!!";
            }
            $this->jsonAjaxResponse($status, $messages);
        }
    }

    protected function _isAuthenticate()
    {
        $user = EasyPics::getUser();
        $config = EasyPics::getAppConfig();

        if (!empty($user->twitter_token)) {

            // Unserialize access token
            $accessToken = unserialize($user->twitter_token);

            $config = $config->twitter->toArray();
            $config['username'] = $accessToken->getParam('screen_name');
            $config['accessToken'] = $accessToken;

            $twitter = new Zend_Service_Twitter($config);
            $response = $twitter->account->verifyCredentials(); // Verify if credentials work

            if (!$response || !empty($response->error) ) {
                return false;
            }
            else {
                return true;
            }
        }
        return false;
    }


}
