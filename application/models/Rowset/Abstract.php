<?php
abstract class Application_Model_Rowset_Abstract extends Zend_Db_Table_Rowset_Abstract
{

    public function init()
    {
        //$this->_user = EasyPics::getUser();
    }


    public function toArray()
    {
        $results = array();
        foreach ($this as $k => $row) {
            array_push($results, $row->toArray());
        }
        return $results;
    }

    public function filteredDatas()
    {
        $filtered = array();
        foreach ($this as $k => $row) {
            array_push($filtered, $row->filteredDatas());
        }
        return $filtered;
    }



}