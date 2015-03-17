<?php


abstract class Application_Model_Row_Abstract extends Zend_Db_Table_Row_Abstract
{

    public function filteredDatas()
    {
        $filter = $this->_getFilter();

        $filtered = array();
        $datas = $this->toArray();

        foreach ($datas as $k => $value) {
            if (in_array($k, $filter)) {
                $filtered[$k] = $value;
            }
        }

        return $filtered;
    }

    protected function _getUser()
    {
        return $this->getTable()->getUser();
    }

    protected function _getTime($datetime)
    {
        $time = null;
        $pattern = "/^(\d{4})\-(\d{2})\-(\d{2})\s(\d{2})\:(\d{2})\:(\d{2})$/";
        if (preg_match($pattern, $datetime, $matches)) {
            $time = mktime($matches[4], $matches[5], $matches[6], $matches[2], $matches[3], $matches[1]);
        }
        return $time;
    }

}	
