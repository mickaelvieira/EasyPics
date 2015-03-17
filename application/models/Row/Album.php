<?php
class Application_Model_Row_Album extends Application_Model_Row_Abstract
{

    const RULE_PICTURES  = 'AlbumsPictures';
    const TABLE_PICTURES = 'Application_Model_DbTable_Pictures';
    const RULE_USERS     = 'UsersAlbums';
    const TABLE_USERS    = 'Application_Model_DbTable_Users';

    public function getUser()
    {
        return $this->findParentRow(self::TABLE_USERS, self::RULE_USERS);
    }

    public function getPictures()
    {

        $user = $this->_getUser();

        if (is_null($user)) {
            return null;
        }

        $select = $this->select();
        $select->where('user = ?', $user->id);
        $select->order('original_date_time DESC');

        return $this->findDependentRowset(self::TABLE_PICTURES, self::RULE_PICTURES, $select);
    }

    public function getPublicPictures()
    {
        $select = $this->select();
        $select->where('privacy = 0');
        $select->order('original_date_time DESC');

        $pictures = $this->findDependentRowset(self::TABLE_PICTURES, self::RULE_PICTURES, $select);
        $results = $pictures->filteredDatas();

        return $results;
    }

    public function setPicturesPrivacy($privacy)
    {
        $user = $this->_getUser();

        if (is_null($user)) {
            return $this;
        }

        $db = $this->getTable()->getAdapter();

        $datas = array('privacy' => $privacy);
        $where = array(
            "album = ?" => $this->id,
            "user = ?" => $user->id
        );
        $db->update('pictures', $datas, $where);

        return $this;
    }


    public function toArray()
    {
        $datas = (array)$this->_data;

        $datas['timestamp'] = $this->_getTime($datas['date_created']);

        return $datas;
    }


    protected function _getFilter()
    {
        return array(
            'id',
            'name'
        );
    }

}