<?php
class EasyPics_View_Helper_AddPicture extends Zend_View_Helper_HtmlElement
{
    
    protected $_formImport;
    
    protected $_formUpload;
    
    protected $_partial;

    public function AddPicture($partial = 'index/forms/add-picture.phtml')
    {            
        $this->_partial = $partial;
        return $this;
    }

    public function render()
    {
        $params = array(
            'formImport' => $this->_formImport,
            'formUpload' => $this->_formUpload
        );
        return $this->view->partial($this->_partial, null, $params);
    }

    public function setFormImport(Application_Form_Import $form)
    {
        $this->_formImport = $form;
    }

    public function setFormUpload(Application_Form_Upload $form)
    {
        $this->_formUpload = $form;
    }

    public function direct()
    {
        return $this->AddPicture();
    }
}
