<?php
class EasyPics_View_Helper_Header extends Zend_View_Helper_HtmlElement
{
    
    
    protected $_title;
    
    protected $_partial;


    public function Header($partial = 'helpers/header.phtml')
    {            
        $this->_partial = $partial;
        return $this;
    }

    public function render()
    {
        $params = array(
            'title' => $this->_title
        );
        return $this->view->partial($this->_partial, null, $params);
    }

    public function setTitle($title)
    {
        $this->_title = $title;
    }

    public function direct()
    {
        return $this->Header();
    }
}
