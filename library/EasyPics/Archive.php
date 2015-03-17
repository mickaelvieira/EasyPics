<?php
class EasyPics_Archive
{

    const GZIP_EXTENSION = 'gz'; 	// cli & php
    const TGZ_EXTENSION  = 'tgz';	// cli
    const ZIP_EXTENSION  = 'zip';	// cli & php
    const BZ2_EXTENSION  = 'bz2';	// none
    const TAR_EXTENSION  = 'tar';	// cli
    const RAR_EXTENSION  = 'rar';	// cli & php

    protected $_mode = "cli";

    protected $_type;

    public function __construct($archive, $destination)
    {
        if (!is_dir($destination)) {
            throw new Zend_Exception('[' . $destination . '] is not a directory');
        }
        if (!is_writable($destination)) {
            throw new Zend_Exception('[' . $destination . '] is not a writable');
        }
        if (!is_file($archive)) {
            throw new Zend_Exception('[' . $archive . '] is not a file');
        }

        $this->_destination = $destination;
        $this->_source = $archive;

        $pathinfos = pathinfo($this->_source);
        $this->_type = mb_convert_case($pathinfos['extension'], MB_CASE_LOWER);

        $this->isValidArchive();
        $this->checkPHPModule();
    }

    public function process()
    {
        EasyPics_Logger::getSingleton()->log("Process extraction [".$this->_mode."] ...");

        if ($this->_mode == "php") {

            EasyPics_Logger::getSingleton()->log("FILTER [".$this->_source."] ...");

            $this->_filter->filter($this->_source);
        }
        else if ($this->_mode == "cli") {

            EasyPics_Logger::getSingleton()->log("CLI [".$this->_cli."] ...");

            exec($this->_cli, $output, $result);
        }
        else {
            throw new Zend_Exception("Invalid mode");
        }
    }

    public function getMode()
    {
        return $this->_mode;
    }

    protected function isValidArchive()
    {
        if ($this->_type != self::GZIP_EXTENSION &&
            $this->_type != self::TGZ_EXTENSION &&
            $this->_type != self::ZIP_EXTENSION &&
            $this->_type != self::TAR_EXTENSION &&
            $this->_type != self::RAR_EXTENSION)
        {
            throw new Zend_Exception("Invalid archive type");
        }
        return true;
    }

    protected function checkPHPModule()
    {
        if ($this->_type == self::GZIP_EXTENSION) {
            if (extension_loaded('zlib')) {
                $this->_mode = "php";
            }
        }
        else if ($this->_type == self::ZIP_EXTENSION) {
            if (extension_loaded('zip')) {
                $this->_mode = "php";
            }
        }
        else if ($this->_type == self::RAR_EXTENSION) {
            if (extension_loaded('rar')) {
                $this->_mode = "php";
            }
        }

        //$this->_mode = "cli"; // force cli

        if ($this->_mode == "php") {
            $this->_filter = $this->getZendFilter();
        }
        else if ($this->_mode == "cli") {
            $this->_cli = $this->getCLI();
        }
        else {
            throw new Zend_Exception("Invalid mode type");
        }
    }

    protected function getCLI()
    {
        $source = escapeshellarg(realpath($this->_source));
        $destination = escapeshellarg(realpath($this->_destination . "/"));

        if ($this->_type == self::GZIP_EXTENSION) {
            $this->_cli = "gzip " . $source . " " . $destination; // TODO : à tester
        }
        else if ($this->_type == self::ZIP_EXTENSION) {
            $this->_cli = "unzip " . $source . " -d " . $destination;
        }
        else if ($this->_type == self::TGZ_EXTENSION) {
            $this->_cli = "tar xzvf " . $source . " -C " . $destination;
        }
        else if ($this->_type == self::TAR_EXTENSION) {
            $this->_cli = "tar xvf " . $source . " -C " . $destination;
        }
        else if ($this->_type == self::BZ2_EXTENSION) {
            $this->_cli = "tar xvjf " . $source . " -C " . $destination;  // TODO : à tester
        }
        else if ($this->_type == self::RAR_EXTENSION) {
            $this->_cli = "unrar command -switch " . $source . " " . $destination; // TODO : à tester
        }

        return $this->_cli;
    }

    protected function getZendFilter()
    {
        // TODO : tester l'utilisation des filtres en php
        $adaptar = ucfirst($this->_type);
        $destination = realpath($this->_destination . "/");

        $this->_filter = new Zend_Filter_Decompress(array(
            'adapter' => $adaptar,
            'options' => array(
                'target' => $destination,
            )
        ));
        return $this->_filter;
    }

}
