<?php
class FormController extends EasyPics_Controller_Action
{
    public function addPictureAction()
    {
        $formImport = new Application_Form_Import();
        $formUpload = new Application_Form_Upload();

        $this->view->AddPicture()->setFormImport($formImport);
        $this->view->AddPicture()->setFormUpload($formUpload);

        $html = $this->view->AddPicture()->render();

        $results = new stdClass();
        $results->html = $html;

        $this->jsonAjaxResponse(true, null, $results);
    }

    /*public function editPictureAction()
    {
        $picture_id = $this->_getParam("id", null);

        if ($this->_request->isXmlHttpRequest() && !is_null($picture_id)) {

            $form = new Application_Form_Picture();

            $pictures = new Application_Model_DbTable_Pictures();
            $where = $pictures->select()
                                ->where('id = ?', $picture_id)
                                ->where('user = ?', $this->_user->id);

            $picture = $pictures->fetchRow($where);


            if (!is_null($picture)) {

                $datas = array(
                    'id'        	=> $picture->id,
                    'album'        	=> $picture->album,
                    'title' 		=> $picture->title,
                    'description' 	=> $picture->description,
                    'privacy' 		=> $picture->privacy
                );
                $form->setDefaults($datas);

                $this->view->EditPicture()->setPicture($picture);
                $this->view->EditPicture()->setForm($form);

                $results = new stdClass();
                $results->html = $this->view->EditPicture()->render();
                $results->datas = $picture->toArray();

                $this->jsonAjaxResponse(true, null, $results);
            }
            else {
                $this->httpCodeResponse(404, "Requested picture ".$picture_id." not found");
            }
        }
    }*/

    /*public function addAlbumAction()
    {

        //if ($this->_request->isXmlHttpRequest()) {

            $form = new Application_Form_Album();



            $this->view->AddAlbum()->setForm($form);

            $results = new stdClass();
            $results->html = $this->view->AddAlbum()->render();

            $this->jsonAjaxResponse(true, null, $results);

        //}


    }*/


}