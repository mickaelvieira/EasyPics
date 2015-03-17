<?php
class Application_Model_DbTable_Pictures extends Application_Model_DbTable_Abstract
{
    protected $_name    = 'pictures';
    
    protected $_primary = array('id');
    
    protected $_rowClass = 'Application_Model_Row_Picture';

    protected $_rowsetClass = 'Application_Model_Rowset_Pictures';

    protected $_referenceMap = array(
        'UsersPictures' => array(
            'columns'       => array('user'),
            'refTableClass' => 'Application_Model_DbTable_Users',
            'refColumns' 	=> array('id'),
        ),
        'AlbumsPictures' => array(
            'columns'       => array('album'),
            'refTableClass' => 'Application_Model_DbTable_Albums',
            'refColumns' 	=> array('id'),
        )
    );

    public function getPicture($picture_id)
    {
        if (is_null($this->_user)) {
            return null;
        }

        $select = $this->select();
        $select->where('id = ?', $picture_id);
        $select->where('user = ?', $this->_user->id);

        return $this->fetchRow($select);
    }

    public function getPictureByKeyUrl($key_url)
    {
        if (is_null($this->_user)) {
            return null;
        }

        $select = $this->select();
        $select->where('key_url = ?', $key_url);
        $select->where('user = ?', $this->_user->id);

        return $this->fetchRow($select);
    }

    public function getPublicPictureByKeyUrl($key_url)
    {
        $select = $this->select();
        $select->where('key_url = ?', $key_url);
        $select->where('privacy = 0');

        return $this->fetchRow($select);
    }

    public function addPicture($datas)
    {
        if (is_null($this->_user)) {
            return $this;
        }

        $datas['user'] = $this->_user->id;
        $datas['key_url'] = $this->_getKeyUrl($datas['path_to_original']);

        $picture = $this->createRow($datas);
        $picture->save();

        return $picture;
    }

    public function updatePicture($picture_id, $datas = array())
    {

        if (is_null($this->_user)) {
            return $this;
        }

        $filter = $this->_getFiltersChain();

        $picture = $this->getPicture($picture_id);

        if (!is_null($picture)) {

            $picture->title = $filter->filter($datas['title']);
            $picture->description = $filter->filter($datas['description']);
            $picture->save();
        }

        return $picture;
    }

    public function deletePicture($picture_id)
    {
        if (is_null($this->_user)) {
            return $this;
        }

        $db = $this->getAdapter();
        $where = array(
            $db->quoteInto('id = ?', $picture_id),
            $db->quoteInto('user = ?', $this->_user->id)
        );
        $this->delete($where);
    }

    public function searchPictures($requestParams)
    {
        if (is_null($this->_user)) {
            return null;
        }

        $where = $this->_getSearchQuery($requestParams);

        if (!($where instanceof Zend_Db_Select)) {
            return null;
        }

        return $this->fetchAll($where);
    }

    public function rangeValues()
    {
        if (is_null($this->_user)) {
            return null;
        }

        $db = $this->getAdapter();
        $select = $db->select();
        $select->from(array('p' => 'pictures'),
                        array('max_aper'       => 'MAX(aperture_value)',
                                'min_aper'     => 'MIN(aperture_value)',
                                'max_exp_time' => 'MAX(exposuretime)',
                                'min_exp_time' => 'MIN(exposuretime)',
                                'max_iso'      => 'MAX(iso_speed_rating) ',
                                'min_iso'      => 'MIN(iso_speed_rating)'
                            ));
        $select->where('user = ?', $this->_user->id);

        return $db->fetchRow($select);
    }


    protected function _getSearchQuery($paramsRequest)
    {

        if (is_null($this->_user)) {
            return $this;
        }

        $adapter = $this->getAdapter();
        $params  = $this->_getMapParams();

        $where = $this->select();
        $where->where('user = ?', $this->_user->id);

        foreach ($params as $k => $param) {

            if (isset($paramsRequest[$param])) {

                $value = $paramsRequest[$param];

                if ($param == "color") {

                    if ($value == 1) {
                        $where->where($k . ' = 1');
                    }
                    else if ($value == 2) { // n/b
                        $where->where($k . ' = 0');
                    }
                }
                else if ($param == 'aperture' || $param == 'exp_time' || $param == 'iso') {

                    $split = preg_split("/\-/", $value);

                    if (count($split)) {

                        $min = $split[0];
                        $max = $split[1];

                        $where->where($k . " >= " . $adapter->quote($min));
                        $where->where($k . " <= " . $adapter->quote($max));
                    }
                }
                else if ($param == "terms") {

                    $split = preg_split("/\s/", $value);
                    $orwhere = array();

                    foreach ($split as $k => $word) {

                        if (strlen($word) > 2) {
                            $word = $adapter->quote("%".$word."%");
                            $orwhere = array(
                                "title LIKE ".$word,
                                "description LIKE ".$word,
                                "manufacturer LIKE ".$word,
                                "model LIKE ".$word
                            );
                        }
                    }
                    $where->where(implode(" OR ", $orwhere));
                }
                else {
                    $where->where($k ." = ?", $value);
                }
            }
        }
        return $where;
    }


        /*

        album     : /([0-9]+)/,
        terms     : /([a-zA-Z0-9]+)/,
        color     : /([0-9]+)/,
        flash     : /([0-9]+)/,
        aperture  : /([0-9\.]+)-([0-9\.]+)/,
        exp_time  : /([0-9\.]+)-([0-9\.]+)/,
        exp_mode  : /([0-9]+)/,
        exp_prod  : /([0-9]+)/,
        light     : /([0-9]+)/,
        white     : /([0-9]+)/,
        iso       : /([0-9]+)-([0-9]+)/
        */





    protected function _getMapParams()
    {
        return array(
            'album'            => 'search_in',
            'terms'            => 'terms',
            'is_color'         => 'color',
            'with_flash'       => 'flash',
            'aperture_value'   => 'aperture',
            'exposuretime'     => 'exp_time',
            'exposuremode'     => 'exp_mode',
            'exposureprogram'  => 'exp_prod',
            'lightsource'      => 'light',
            'whitebalance'     => 'white',
            'iso_speed_rating' => 'iso'
        );
    }

    protected function _getKeyUrl($path)
    {
        if (is_null($this->_user)) {
            return $this;
        }

        $filter = new EasyPics_Filter_StringUrl();
        $path = $filter->filter($path);

        return md5($this->_user->id . "-" . $path . "-" . date("Y-m-d H:i:s"));
    }

    protected function _getFiltersChain()
    {
        $filter = new Zend_Filter();
        $filter->addFilter(new Zend_Filter_StringTrim());
        $filter->addFilter(new Zend_Filter_StripTags());

        return $filter;
    }

}