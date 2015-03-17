<?php
class Application_Model_DbTable_Albums extends Application_Model_DbTable_Abstract
{
    protected $_name    = 'albums';
    
    protected $_primary = array('id');
    
    protected $_rowClass = 'Application_Model_Row_Album';

    protected $_rowsetClass = 'Application_Model_Rowset_Albums';

    protected $_dependentTables = array("Application_Model_DbTable_Pictures");
    
    protected $_referenceMap = array(
        'UsersAlbums' => array(
            'columns'       => array('user'),
            'refTableClass' => 'Application_Model_DbTable_Users',
            'refColumns' 	=> array('id'),
        )
    );
    
    public function getAlbums()
    {

        if (is_null($this->_user)) {
            return null;
        }

        $select = $this->select();
        $select->where('user = ?', $this->_user->id);
        $select->order('date_created DESC');

        return $this->fetchAll($select);
    }

    public function getAlbum($album_id)
    {

        if (is_null($this->_user)) {
            return null;
        }

        $select = $this->select();
        $select->where('id = ?', $album_id);
        $select->where('user = ?', $this->_user->id);

        return $this->fetchRow($select);
    }

    public function getPublicAlbum($album_id)
    {

        $select = $this->select();
        $select->where('id = ?', $album_id);
        $select->where('privacy = 0');

        return $this->fetchRow($select);
    }

    public function getPublicAlbumByKeyUrl($key_url)
    {

        $select = $this->select();
        $select->where('key_url = ?', $key_url);
        $select->where('privacy = 0');

        return $this->fetchRow($select);
    }

    public function addAlbum($datas = array())
    {

        if (is_null($this->_user)) {
            return $this;
        }

        $filter = $this->_getFiltersChain();

        if (!is_array($datas)) {
            $datas = array();
        }

        if (isset($datas['name']) && !empty($datas['name'])) {
            $name = $filter->filter($datas['name']);
        }
        else {
            $name = $this->_getDefaultName();
        }

        $album = $this->createRow();

        $album->key_url      = $this->_getKeyUrl($name);
        $album->user         = $this->_user->id;
        $album->name         = $name;
        $album->privacy      = 1;
        $album->date_created = new Zend_Db_Expr('NOW()');
        $album->save();

        return $album;
    }

    public function updateAlbum($album_id, $datas = array())
    {

        if (is_null($this->_user)) {
            return $this;
        }

        $filter = $this->_getFiltersChain();

        if (!is_array($datas)) {
            $datas = array();
        }

        $album = $this->getAlbum($album_id);

        if (!is_null($album)) {

            $album->name          = $filter->filter($datas['name']);
            $album->description   = $filter->filter($datas['description']);
            $album->privacy       = $datas['privacy'];
            $album->cover         = $datas['cover'];
            $album->date_modified = new Zend_Db_Expr('NOW()');
            $album->save();
        }

        return $album;
    }

    public function deleteAlbum($album_id)
    {
        if (is_null($this->_user)) {
            return $this;
        }

        $db = $this->getAdapter();
        $where = array(
            $db->quoteInto('id = ?', $album_id),
            $db->quoteInto('user = ?', $this->_user->id)
        );
        $this->delete($where);

        return $this;
    }

    protected function _getDefaultName()
    {
        return "Untitled - ".date("Y-m-d H:i:s");
    }

    protected function _getFiltersChain()
    {
        $filter = new Zend_Filter();
        $filter->addFilter(new Zend_Filter_StringTrim());
        $filter->addFilter(new Zend_Filter_StripTags());

        return $filter;
    }

    protected function _getKeyUrl($name)
    {
        if (is_null($this->_user)) {
            return $this;
        }

        $filter = new EasyPics_Filter_StringUrl();
        $path = $filter->filter($name);

        return md5($this->_user->id . "-" . $name . "-" . date("Y-m-d H:i:s"));
    }

}
