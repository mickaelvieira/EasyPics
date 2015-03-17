<?php
class EasyPics_View_Helper_Stylesheet extends Zend_View_Helper_Abstract {
     
    protected $_paths = array();

    protected $_styles = "";

    public function Stylesheet()
    {
        $this->_styles = $this->view->headLink();
        foreach ($this->_paths as $k => $path) {
            $this->_styles->appendStylesheet($this->view->baseUrl($path, "screen"));
        }
        return $this;
    }

    public function setPaths($paths = array())
    {
        $this->_paths = $paths;
    }

    public function render()
    {
        return $this->_styles;
    }

    public function direct()
    {
        return $this->Stylesheet();
    }
}
