<?php
class EasyPics_View_Helper_Meta extends Zend_View_Helper_HtmlElement {


    protected $_partial;


    public function Meta($partial = 'includes/meta.phtml')
    {
        $this->_partial = $partial;
        return $this;
    }

    public function render()
    {
        $params = array();
        return $this->view->partial($this->_partial, null, $params);
    }

    public function direct()
    {
        return $this->Meta();
    }
}
