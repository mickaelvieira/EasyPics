<?php
class EasyPics_Controller_Action_Helper_Image extends Zend_Controller_Action_Helper_Abstract
{

    protected $_imageType = array();


    public function __construct()
    {
        $this->_initImagesConfig();
    }

    protected function _initImagesConfig()
    {
        $config = EasyPics::getAppConfig();

        if (!isset($config->easypics->imgSettings)) {
            throw new Zend_Exception("Images configuration are not defined");
        }

        if (isset($config->easypics->imgSettings)) {

            foreach ($config->easypics->imgSettings as $type => $config) {

                if (!array_key_exists($type, $this->_imageType)) {
                    $this->_imageType[$type] = new stdClass();
                }

                $this->_imageType[$type]->width = $config->width;
                $this->_imageType[$type]->height = $config->height;
                $this->_imageType[$type]->type = $config->type;
                $this->_imageType[$type]->compression = $config->compression;
            }
        }
    }

    public function getImageConfig($type)
    {
        if (array_key_exists($type, $this->_imageType)) {

            $config = $this->_imageType[$type];

            return $config;
        }
        return null;
    }

}
