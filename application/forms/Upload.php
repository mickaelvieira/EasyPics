<?php
class Application_Form_Upload extends Zend_Form
{

    public function __construct() {

        parent::__construct();

        $view = $this->getView();
        $this->setDisableLoadDefaultDecorators(true);

        $this->setName("form_upload");
        $this->setAttrib("id", "form-upload");
        $this->setAction($view->baseUrl('action/upload/')); //$view->baseUrl('import/upload/')
        $this->setEnctype("application/x-www-form-urlencoded");
        $this->setMethod("post");
        $this->setAttrib("target", "form_add_iframe");

        $this->setDecorators(array('FormElements', 'Form'));

        /*$this->setDecorators(array(
            array('ViewScript', array('viewScript' => 'import/includes/upload.phtml'))
        ));*/

        $album_type = new Zend_Form_Element_Radio('album_type');
        $album_type->setDisableLoadDefaultDecorators(true);
        $album_type->setOptions(
            array(
                'label'      => 'Type Import',
                'id'         => 'upload_album_type',
                'name'       => 'upload_album_type',
                'required'   => true,
                'filters'    => array(),
                'decorators' => array(
                    'ViewHelper',
                    array('HtmlTag', array('tag' => 'div', 'id' => 'upload-album-type', 'class' => 'clearfix element-container'))
                ),
                'multiOptions' => array(				    
                    "0" => "Album existant",
                    "1" => "Nouvel album"
                )
            )
        );

        $album_id = new Zend_Form_Element_Select('album_id');
        $album_id->setOptions(
            array(
                'label'      => 'Choisissez un album',
                'id'         => 'upload_album_id',
                'name'       => 'upload_album_id',
                'required'   => true,
                'filters'    => array(),
                'decorators' => array(
                    'ViewHelper',
                    'Label',
                    array('HtmlTag', array('tag' => 'div', 'id' => 'upload-album-id-container', 'class' => 'clearfix element-container'))
                ),
                'multiOptions' => array()        
            )
        );

        $album_name = new Zend_Form_Element_Text('album_name');
        $album_name->setOptions(
            array(
                'label'      => 'Nom de l\'album',
                'id'         => 'upload_album_name',
                'name'       => 'upload_album_name',
                'required'   => true,
                'filters'    => array('StringTrim','StripTags'),
                'decorators' => array(
                    'ViewHelper',
                    'Label',
                    array('HtmlTag', array('tag' => 'div', 'id' => 'upload-album-name-container', 'class' => 'clearfix element-container'))
                )
            )
        );

        $file = new Zend_Form_Element_File('file');
        $file->setOptions(
            array(
                'label'      => 'Fichier',
                'id'         => 'upload_file',
                'name'       => 'upload_file',
                'required'   => true,
                'filters'    => array(),
                'decorators' => array(
                    'File',
                    'Label',
                    array('HtmlTag', array('tag' => 'div', 'class' => 'clearfix element-container'))
                )
            )
        );
        $file->setMaxFileSize(10000000);


        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setOptions(
            array(
                'label' => 'Upload',
                'id'    => 'form-upload-submit-button',
                'name'  => 'form_upload_submit_button',
                'attribs'    => array("class" => "btn"),
                'decorators' => array(
                    'ViewHelper',
                    array('HtmlTag', array('tag' => 'div', 'class' => 'clearfix element-container'))
                )
            )
        );

        $this->addElement($album_type);
        $this->addElement($album_id);
        $this->addElement($album_name);
        $this->addElement($file);
        $this->addElement($submit);

    }
}
