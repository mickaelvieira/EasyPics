<?php
class SearchController extends EasyPics_Controller_Action {


    function init() {

    }

    public function indexAction() {

        $this->_helper->layout->setLayout("search");

        //exit('ma');

        //$cache_key = "search_index";
        //$html = $this->cache->load($cache_key);

        //if ($html === false || !$this->_appConfig->cache->enable) {

        //	$html = $this->view->render("index.phtml");
        //	$this->cache->save($html, $cache_key);
        //}
//
        //echo $html;
        //exit;
    }

    public function searchAction() {

        //if ($this->_request->isXmlHttpRequest()) {

            $request = $this->getRequest();

            if ($request->isPost()) {
                $datas = $request->getPost();

                var_dump($datas);
                exit;

            }
        
      




            /*$collection = new EasyPics_Models_Album_Collection();
            $collection->addEntryToSelect("*");
            $collection->addAttributeToWhereClause("user_id = ".$this->_db->quote($this->_user->id));
            $collection->addOrderToSelect("date_created DESC");
            $collection->addLimitToSelect(1, 10);// à ajouter dans la config utilisateur
            $collection->loadCollection();

            $albums = $collection->getCollection();

            $json_albums = array();
            foreach ($albums as $album) {

                $json_album = new stdClass();
                $json_album->id = $album->getId();
                $json_album->name = $album->name;
                $json_album->key_url = $album->key_url;
                $json_album->description = $album->description;
                $json_album->visible = $album->visible;
                $json_album->private = $album->private;
                $json_album->is_built = $album->is_built;
                $json_album->date_created = $album->date_created;
                $json_album->date_modified = $album->date_modified;
                $json_album->cover_url = $this->_baseUrl . "picture/small/id/".$album->cover_id;
                $json_album->delete_url = $this->_baseUrl . "album/delete/id/".$album->getId();
                $json_album->edit_url = $this->_baseUrl . "album/get.Form/id/".$album->getId();
                $json_album->album_url = $this->_baseUrl . "index/album/id/".$album->getId();

                array_push($json_albums, $json_album);
            }


            //$this->view->json_albums = Zend_Json::encode($json_albums);


            $today = time();

            $maxTime = $this->_appConfig->cache->json->lifetime;
            $serverTime = gmdate("D, d M Y H:i:s", $today) . ' GMT';
            $expireTime = gmdate('D, d M Y H:i:s', $today + $maxTime) . ' GMT';

            $this->_response->setHeader('Content-Type', 'application/json', true)
                            ->setHeader('Cache-Control', 'max-age=' . $maxTime, true)
                            ->setHeader('Cache-Control', 'public', true)
                            ->setHeader('Pragma', '', true)
                            ->setHeader('Date', $serverTime, true)
                            ->setHeader('Expires', $expireTime, true)
                            ->setBody(Zend_Json::encode($json_albums));	*/


        }

    //}


}
?>
