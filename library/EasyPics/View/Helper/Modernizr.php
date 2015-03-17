<?php
class EasyPics_View_Helper_Modernizr extends Zend_View_Helper_HtmlElement {


    protected $_partial;


    public function Modernizr($partial = 'includes/modernizr.phtml')
    {	
        $this->_partial = $partial;
        return $this;
    }

    public function render()
    {
        $params = array(


        );
        return $this->view->partial($this->_partial, null, $params);
    }

    public function direct()
    {
        return $this->Modernizr();
    }
}
