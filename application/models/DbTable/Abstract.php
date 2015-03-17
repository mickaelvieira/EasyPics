<?php
abstract class Application_Model_DbTable_Abstract extends Zend_Db_Table_Abstract
{

    public function init()
    {
        $this->_user = EasyPics::getUser();
    }

    public function getUser()
    {
        return $this->_user;
    }

    public function setUser($user)
    {
        $this->_user = $user;
    }

}
