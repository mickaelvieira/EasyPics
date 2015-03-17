<?php
class EasyPics_File_File
{

    protected $_path;


    protected $_dirname;


    protected $_basename;


    protected $_filename;


    protected $_extension;


    protected $_size;


    protected $_isDir = false;


    protected $_isFile = false;


    protected $_deleted = false;


    public function __construct($path)
    {
        $this->_path = preg_replace("/".preg_quote(DIRECTORY_SEPARATOR, "/")."/", "/", $path);
        $this->getInfos();
    }

    public function getInfos()
    {
        if (is_dir($this->_path)) {
            $this->_isDir = true;
        }
        else if (is_file($this->_path)) {
            $this->_isFile = true;
        }
        else {
            throw new Zend_Exception('['.$this->_path.'] is not a directory or a file');
        }

        $pathinfos = pathinfo($this->_path);
        $this->_basename = $pathinfos['basename'];
        $this->_filename = $pathinfos['filename'];
        $this->_dirname = $pathinfos['dirname'];
        $this->_size = filesize($this->_path);

        if ($this->_isFile) {
            $this->_extension = mb_convert_case($pathinfos['extension'], MB_CASE_LOWER);
        }
    }

    public function isFile()
    {
        return $this->_isFile;
    }

    public function isDir()
    {
        return $this->_isDir;
    }

    public function hasBeenDeleted()
    {
        $this->_deleted;
    }

    public function getSize()
    {
        return $this->_size;
    }

    public function getBasename()
    {
        return $this->_basename;
    }

    public function getDirname()
    {
        return $this->_dirname;
    }

    public function getFilename()
    {
        return $this->_filename;
    }

    public function getExtension()
    {
        return $this->_extension;
    }

    public function getPath()
    {
        return $this->_path;
    }

    public function getRelativePath($baseUrl)
    {
        $filter = new EasyPics_Filter_RelativeUrl($baseUrl);
        return $filter->filter($this->getPath());
    }

    public function copy($destination, $override = false)
    {
        $pathinfos = pathinfo($destination);
        if (isset($pathinfos['dirname'])) {
            $dirname = $pathinfos['dirname'];
            $basename = $pathinfos['basename'];
            $filename = $pathinfos['filename'];
            $extension = mb_convert_case($pathinfos['extension'], MB_CASE_LOWER);

            if (!is_dir($dirname)) {
                throw new Zend_Exception('['.$dirname.'] is not a directory');
            }
            if (is_file($destination)) {

                EasyPics_Logger::getSingleton()->log("File exists - try a new name");
                EasyPics_Logger::getSingleton()->log("------------------------------------------------");

                if (!preg_match("/\/$/", $dirname)) {
                    $dirname .= "/";
                }

                $counter = 1;

                $destination = $dirname . $filename . "-".$counter.".".$extension;

                EasyPics_Logger::getSingleton()->log("try ".$destination);

                //print "try ".$destination."\n";

                while (is_file($destination)) {

                    $counter++;
                    $destination = $dirname . $filename . "-".$counter.".".$extension;

                    EasyPics_Logger::getSingleton()->log("try ".$destination);

                    //print "try ".$destination."\n";

                }
                //print "OK w/ the name ".$destination;

                EasyPics_Logger::getSingleton()->log("OK w/ the name ".$destination);
                EasyPics_Logger::getSingleton()->log("------------------------------------------------");
                //$destination = $dirname . $filename . "-1"


                //throw new Zend_Exception('['.$basename.'] already exists in directory ['.$dirname.']');
            }
            if (@copy($this->_path, $destination)) {

                //print "Copy ".$this->_path." => ".$destination;

                EasyPics_Logger::getSingleton()->log("Copy ".$this->_path." => ".$destination);

                return $destination;
            }
        }
        return false;
    }

    public function move($destination, $override = false)
    {
        if ($this->copy($destination, $override)) {
            if (@unlink($this->_path)) {
                $this->_path = $destination;
                $this->getInfos();
                return true;
            }
        }
        return false;
    }

    public function delete()
    {

        EasyPics_Logger::getSingleton()->log("Delete ".$this->_path);

        if (@unlink($this->_path)) {

            $this->_path = null;
            $this->_basename = null;
            $this->_filename = null;
            $this->_dirname = null;
            $this->_size = null;
            $this->_deleted = true;

            return true;
        }
        return false;
    }

}