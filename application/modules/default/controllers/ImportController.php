<?php
class ImportController extends EasyPics_Controller_Action {

    protected $_tempDirPath = "";

    protected $_dirPath;

    //protected $_dirContent = array();

    protected $_tempDirectory;

    protected $_importDirectory;

    public function init() {

    }

    public function indexAction()
    {
        exit;
                /*
        $this->_helper->layout->setLayout("import");

        $auth = Zend_Auth::getInstance();
        $form = new Application_Form_Import();


        $users = new Application_Model_DbTable_Users();
        $user = $users->find($auth->getStorage()->read()->id)->current();
        $select = $users->select()->order('name ASC');
        $albums = $user->findDependentRowset('Application_Model_DbTable_Albums', 'UsersAlbums', $select);

        $select_albums = array();
        foreach ($albums as $album) {
            $select_albums[$album->id] = $album->name;
        }
        $form->getElement('album_id')->options = $select_albums;
        $this->view->form = $form;

        /*$cache_key = "import_index";

        $cache = EasyPics_Cache_Manager::getCache();


        $html = $cache->load($cache_key);

        if ($html === false || !$this->_appConfig->cache->enable) {

            $html = $this->view->render("index.phtml");
            $this->cache->save($html, $cache_key);
        }*/

        //echo $html;
        //exit;
    }

    /* liste le contenu du répertoire d'importation */
    public function contentAction()
    {
        $dirContent = array();
        $status = false;
        $message = array();

        $front = Zend_Controller_Front::getInstance();

        $docRoot = preg_quote($_SERVER['DOCUMENT_ROOT'] . $front->getBaseUrl(), "/");

        if ($this->_request->isXmlHttpRequest()) {

            $filter = new EasyPics_Filter_StringUrl();

        //	var_dump(Zend_Registry::get("import_dir"));

            $importDirectory = EasyPics_File::factory(Zend_Registry::get("import_dir"));
            $filesList = $importDirectory->read(array("jpg", "gz", "zip", "tgz"), true);

            foreach ($filesList as $k => $path) {

                $file = new stdClass();
                $path_parts = pathinfo($path);

                $file->id = $k + 1;
                $file->ext = mb_convert_case($path_parts['extension'], MB_CASE_LOWER);
                $file->path = preg_replace("/^".$docRoot."/", "", $path);
                $file->filename = $path_parts['filename'];
                $file->basename = $path_parts['basename'];
                $file->key = $filter->filter($path_parts['basename']) ."-" . ($k + 1);

                array_push($dirContent, $file);
            }

            $status	= true;
            $messages[] = "Nombre de fichiers trouvés ".count($dirContent);
        }
        $this->jsonAjaxResponse($status, $messages, $dirContent);
    }

}