<?php
class Api_SearchController extends EasyPics_Rest_Controller
{
    public function indexAction()
    {
        $paramsRequest = $this->_getAllParams();

        $pictures = EasyPics::getModel('pictures');

        $search = $pictures->searchPictures($paramsRequest);

        $results = array();
        foreach ($search as $k => $picture) {
            array_push($results, $picture->toArray());
        }
        $this->jsonAjaxResponse(true, array(), $results);
    }

}
