<?php

class EasyPics_Filter_StringUrl implements Zend_Filter_Interface
{

    protected $_search = array("ç","æ","œ","á","é","í","ó","ú","à","è","ì","ò","ù","ä","ë","ï","ö","ü","ÿ","â","ê","î","ô","û","å","e","i","ø","u");

    protected $_replace = array("c","ae","oe","a","e","i","o","u","a","e","i","o","u","a","e","i","o","u","y","a","e","i","o","u","a","e","i","o","u");

    public function __construct($options = null)
    {
    
    }
        
    public function filter($value)
    {		
        $value = trim($value);

        $value = html_entity_decode($value, ENT_COMPAT,'UTF-8');
        $value = mb_convert_case($value, MB_CASE_LOWER,'UTF-8');

        $value = str_replace($this->_search, $this->_replace, $value);

        $value = preg_replace("/\s{1,}/","-", $value);
        $value = preg_replace("/([^a-z0-9-])/", "", $value);
        $value = preg_replace("/([-]{1,})/", "-", $value);

        return $value;
    }	
}