<?php
class IndexController extends EasyPics_Controller_Action
{

    public function indexAction()
    {
        //print __METHOD__;
        //exit();

        $user = EasyPics::getUser();
        $config = EasyPics::getAppConfig();

        $pictures = EasyPics::getModel('pictures');
        $searchConfig = $pictures->rangeValues();

      //  var_dump(file_get_contents(APPLICATION_PATH . "/conf/photos.ini"));
      //  exit;
        $photoConfig = new Zend_Config_Ini(APPLICATION_PATH . "/conf/photos.ini");
        
        /*foreach ($photoConfig->lightsources as $key => $value) {
            var_dump(ucwords(preg_replace("/\_/", " ", $key)));
            var_dump($value);
        }
        

        exit;*/
        //print __METHOD__;
        //var_dump($searchConfig);
        //exit;

        $this->view->twitter_enable = ($user->twitter_token) ? true : false;
        $this->view->facebook_enable = false;
        $this->view->searchConfig = $searchConfig;
        //$this->view->photoConfig = $photoConfig->toArray(); //voir le probleme sur le fichier ini

        $this->_helper->layout->setLayout("index");
        $this->view->Header()->setTitle($config->app->name);
        $this->view->Stylesheet()->setPaths(array(
            "public/css/easypics.css"
        ));
        $this->view->Javascript()->setPaths(array(
            "public/js/libs/head.js",
            "public/js/scripts/easypics.js"
        ));
    }

    public function loginAction()
    {
        $this->_helper->layout->setLayout("login");
        $this->view->Stylesheet()->setPaths(array(
            "public/css/easypics.css"
        ));

        //print __METHOD__;

        $form = EasyPics::getForm("login");

        if ($this->_request->isPost()) {

            $postData = $this->_request->getPost();

            if ($form->isValid($postData)) {

                $login = $postData['login'];
                $password = $postData['password'];

                $db = Zend_Db_Table::getDefaultAdapter();
                $db->setFetchMode(Zend_Db::FETCH_OBJ);

                $authAdapter = new Zend_Auth_Adapter_DbTable($db);
                $authAdapter->setTableName('users');
                $authAdapter->setIdentityColumn('username');
                $authAdapter->setCredentialColumn('password');
                $authAdapter->setIdentity($login);
                $authAdapter->setCredential($password);
                $authAdapter->setCredentialTreatment('SHA1(CONCAT(?,salt))');
                //http://akrabat.com/zend-auth-tutorial/
                // -- Must be change wrong way to use a salt http://crackstation.net/hashing-security.htm !!!

                $auth = Zend_Auth::getInstance();
                $result = $auth->authenticate($authAdapter);

                if ($result->isValid()) {

                    $auth->getStorage()->write($authAdapter->getResultRowObject(null, 'password'));
                    $this->_redirect('/');
                }
                else {
                    $form->populate($postData);
                }
            }
            else {
                $form->populate($postData);
            }
        }
        $this->view->form = $form;
    }

    public function logoutAction()
    {
        Zend_Auth::getInstance()->clearIdentity();
        $this->_redirect('/'); // back to login page
    }
}
