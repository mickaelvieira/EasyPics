<?php
class Application_Form_Album extends Zend_Form
{

    public function __construct() {

        parent::__construct();

        $view = $this->getView();

        $this->setName("form_album");
        $this->setAttrib("id", "form_album");
        $this->setAction($view->baseUrl('album/edit/'));
        $this->setEnctype("application/x-www-form-urlencoded");
        $this->setMethod("post");
        $this->setDecorators(array('FormElements', 'Form'));

        $album_id = new Zend_Form_Element_Hidden('album_id');
        $album_id->setOptions(
            array(
                'required'   => true,
                'decorators' => array('ViewHelper')
            )
        );

        $name = new Zend_Form_Element_Text('name');
        $name->setOptions(
            array(
                'label'      => 'Name',
                'id'         => 'name',
                'name'       => 'name',
                'required'   => true,
                'filters'    => array('StringTrim','StripTags'),
                'decorators' => array('ViewHelper', 'Label')
            )
        );

        $description = new Zend_Form_Element_Textarea('description');
        $description->setOptions(
            array(
                'label'      => 'Description',
                'id'         => 'description',
                'name'       => 'description',
                'required'   => true,
                'attribs'    => array('rows' => 4, 'cols' => 20),
                'filters'    => array('StringTrim','StripTags'),
                'decorators' => array('ViewHelper', 'Label')
            )
        );

        $key = new Zend_Form_Element_Text('key');
        $key->setOptions(
            array(
                'label'      => 'Key',
                'id'         => 'key',
                'name'       => 'key',
                'required'   => true,
                'filters'    => array('StringTrim','StripTags'),
                'decorators' => array('ViewHelper', 'Label')
            )
        );

        $private = new Zend_Form_Element_Radio('private');
        $private->setOptions(
            array(
                'label'      => 'Private',
                'id'         => 'private',
                'name'       => 'private',
                'required'   => true,
                'filters'    => array(),
                'decorators' => array('ViewHelper', 'Label'),
                'multiOptions' => array(				    
                    "1" => "Yes",
                    "0" => "No"
                )
            )
        );

        $visible = new Zend_Form_Element_Radio('visible');
        $visible->setOptions(
            array(
                'label'      => 'Visible',
                'id'         => 'visible',
                'name'       => 'visible',
                'required'   => true,
                'filters'    => array(),
                'decorators' => array('ViewHelper', 'Label'),
                'multiOptions' => array(				    
                    "1" => "Yes",
                    "0" => "No"
                )
            )
        );

        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setOptions(
            array(
                'label' => 'Submit',
                'decorators' => array('ViewHelper')
            )
        );

        $this->addElement($album_id);
        $this->addElement($name);
        $this->addElement($description);
        $this->addElement($key);
        $this->addElement($private);
        $this->addElement($visible);
        $this->addElement($submit);		
    }
}
