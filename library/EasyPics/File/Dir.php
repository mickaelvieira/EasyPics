<?php
class EasyPics_File_Dir extends EasyPics_File_File
{

    protected $_includes = array();

    //protected $_exclude = array();

    protected $_path = "";

    protected $_recursive = true;

    protected $_content = array();

    public function __construct($path)
    {
        parent::__construct($path);
    }

    public function read($includes = array(), $recursive = true, $force = false)
    {
        if (is_string($includes)){
            $includes = array($includes);
        }

        if (!is_array($includes)) {
            throw new Zend_Filter_Exception("[".$includes."] is not a Array");	
        }

        $this->_recursive = $recursive;
        $this->_includes = $includes;

        if (empty($this->_content) || $force) {
            $this->_readDir($this->_path);
        }

        return $this->_content;
    }

    public function refresh()
    {
        $this->_readDir($this->_path);
    }

    protected function _readDir($dir)
    {
        if (!preg_match("/\/$/", $dir)) {
            $dir = $dir."/";
        }

        if ($dh = opendir($dir)) {
            while (($file = readdir($dh)) !== false) {

                if (($file != ".") && ($file != "..")) {

                    if (is_file($dir . $file)) {

                        $path_parts = pathinfo($file);
                        $ext = mb_convert_case($path_parts['extension'], MB_CASE_LOWER);
                        $name = $path_parts['filename'];



                            /*print "---------------------------\n";
                            print "File : ".$dir . $file."\n";
                            print "Ext : ".$ext."\n";
                            print "Name : ".$name."\n";*/


                        if (is_array($this->_includes) && in_array($ext, $this->_includes)) {
                            array_push($this->_content, $dir . $file);
                        }
                    }
                    else if (is_dir($dir . $file)) {



                        if ($this->_recursive) {
                            $this->_readDir($dir . $file);
                        }
                    }
                }
            }
            closedir($dh);
        }
    }

    public function clean()
    {
        $this->_clean($this->_path);
    }

    protected function _clean($dir)
    {
        if (!preg_match("/\/$/", $dir)) {
            $dir = $dir . "/";
        }

        if (is_dir($dir)) {
            if ($dh = opendir($dir)) {
                while (($file = readdir($dh)) !== false) {
                    if (($file != ".") && ($file != "..")) {
                        if (is_file($dir . $file)) {



                            //print "Delete File : ".$dir . $file."\n";

                            unlink($dir . $file);
                        }
                        else if (is_dir($dir . $file)) {
                            $this->_clean($dir . $file);



                            //print "Remove Dir : ".$dir . $file."\n";

                            rmdir($dir . $file);
                        }
                    }
                }
                closedir($dh);
            }
        }
    }


}
