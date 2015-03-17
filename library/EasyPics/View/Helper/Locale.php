<?php
class EasyPics_View_Helper_Locale extends Zend_View_Helper_HtmlElement
{
    
    protected $_locale = "fr";
    
    
    protected $_partial;


    public function Locale($partial = 'includes/locale.phtml')
    {            
        $this->_partial = $partial;
        return $this;
    }

    public function render()
    {
        $params = array(
            'locale'   => $this->_locale
        );
        return $this->view->partial($this->_partial, null, $params);
    }

    public function setLocale($locale = "fr")
    {
        $this->_locale = $locale;
    }

    public function direct()
    {
        return $this->Locale();
    }
}
