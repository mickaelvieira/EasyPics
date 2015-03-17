<?php
class Application_Model_DbTable_Users extends Zend_Db_Table_Abstract
{
    protected $_name    = 'users';
    
    protected $_primary = array('id');
        
    protected $_dependentTables = array(
        "Application_Model_DbTable_Albums",
        "Application_Model_DbTable_Pictures"
    );

    public function getUsers()
    {

        $select = $this->select();
        $select->order('date_created DESC');

        return $this->fetchAll($select);
    }

    public function getUserByLogin($login)
    {

        $select = $this->select();
        $select->where('username = ?', $login);

        return $this->fetchRow($select);

    }


}
