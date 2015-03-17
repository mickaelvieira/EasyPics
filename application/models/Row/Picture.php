<?php
class Application_Model_Row_Picture extends Application_Model_Row_Abstract
{


    const RULE_USERS   = 'UsersPictures';
    const TABLE_USERS  = 'Application_Model_DbTable_Users';
    const RULE_ALBUMS  = 'AlbumsPictures';
    const TABLE_ALBUMS = 'Application_Model_DbTable_Albums';

    public function toArray()
    {
        $datas = (array)$this->_data;

        $datas['timestamp'] = $this->_getTime($datas['original_date_time']);

        return $datas;
    }

    public function getUser()
    {
        return $this->findParentRow(self::TABLE_USERS, self::RULE_USERS);
    }

    public function getAlbum()
    {
        return $this->findParentRow(self::TABLE_ALBUMS, self::RULE_ALBUMS);
    }

    protected function _getFilter()
    {
        return array(
            'id',
            'key_url',
            'name',
            'title',
            'description',
            'optimized_width',
            'optimized_height'
        );

    }

}