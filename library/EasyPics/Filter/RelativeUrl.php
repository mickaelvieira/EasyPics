<?php

class EasyPics_Filter_RelativeUrl implements Zend_Filter_Interface
{

    protected $_root;

    public function __construct($baseUrl)
    {
        $this->_root = preg_quote(EasyPics::getAppRoot(), "/");
    }
        
    public function filter($value)
    {
        return preg_replace("/" . $this->_root . "/", "", $value);
    }
}