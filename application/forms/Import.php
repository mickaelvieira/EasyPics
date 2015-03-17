<?php
class Application_Form_Import extends Zend_Form
{

    public function __construct() {

        parent::__construct();

        $view = $this->getView();
        $this->setDisableLoadDefaultDecorators(true);

        $this->setName("form_import");
        $this->setAttrib("id", "form-import");
        $this->setAction("#"); //$view->baseUrl('import/import/')
        $this->setEnctype("application/x-www-form-urlencoded");
        $this->setMethod("post");
        $this->setDecorators(array('FormElements', 'Form'));
        /*$this->setDecorators(array(
            array('ViewScript', array('viewScript' => 'import/includes/import.phtml'))
        ));*/

        $album_type = new Zend_Form_Element_Radio('album_type');
        $album_type->setOptions(
            array(
                'label'      => 'Type Import',
                'id'         => 'import_album_type',
                'name'       => 'import_album_type',
                'required'   => true,
                'filters'    => array(),
                'decorators' => array(
                    'ViewHelper',
                    array('HtmlTag', array('tag' => 'div', 'id' => 'import-album-type', 'class' => 'clearfix element-container'))
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
                'id'         => 'import_album_id',
                'name'       => 'import_album_id',
                'required'   => true,
                'filters'    => array(),
                'decorators' => array(
                    'ViewHelper',
                    'Label',
                    array('HtmlTag', array('tag' => 'div', 'id' => 'import-album-id-container', 'class' => 'clearfix element-container'))
                ),
                'multiOptions' => array()        
            )
        );

        $album_name = new Zend_Form_Element_Text('album_name');
        $album_name->setOptions(
            array(
                'label'      => 'Nom de l\'album',
                'id'         => 'import_album_name',
                'name'       => 'import_album_name',
                'required'   => true,
                'filters'    => array('StringTrim','StripTags'),
                'decorators' => array(
                    'ViewHelper',
                    'Label',
                    array('HtmlTag', array('tag' => 'div', 'id' => 'import-album-name-container', 'class' => 'clearfix element-container'))
                )
            )
        );

        /*$archives = new Zend_Form_Element_Radio('archives');
        $archives->setOptions(
            array(
                'label'      => 'Archive',
                'id'         => 'archives',
                'name'       => 'archives',
                'required'   => true,
                'filters'    => array(),
                'decorators' => array('ViewHelper', 'Label'),
                'multiOptions' => array()                 
            )
        );*/

        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setOptions(
            array(
                'label' => 'Import',
                'id'    => 'form-import-submit-button',
                'name'  => 'form_import_submit_button',
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
       // $this->addElement($archives);
        /*$this->addElement($key);
        $this->addElement($private);
        $this->addElement($visible);*/
        $this->addElement($submit);

    }
}
