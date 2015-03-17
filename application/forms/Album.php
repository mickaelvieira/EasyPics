<?php
class Application_Form_Album extends Zend_Form
{

    public function __construct() {

        parent::__construct();

        $view = $this->getView();

        $this->setName("form_edit_album");
        $this->setAttrib("id", "form-edit-album");
        $this->setAction($view->baseUrl('album/edit/'));
        $this->setEnctype("application/x-www-form-urlencoded");
        $this->setMethod("post");
        $this->setDecorators(array('FormElements', 'Form'));

        $album_id = new Zend_Form_Element_Hidden('album_id');
        $album_id->setOptions(
            array(
                'required'   => true,
                'id'         => 'id',
                'name'       => 'id',
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
                'decorators' => array(
                    'ViewHelper',
                    'Label',
                    array('HtmlTag', array('tag' => 'div', 'id' => 'edit-album-name-container', 'class' => 'clearfix element-container'))
                ),
            )
        );

        /*$description = new Zend_Form_Element_Textarea('description');
        $description->setOptions(
            array(
                'label'      => 'Description',
                'id'         => 'description',
                'name'       => 'description',
                'required'   => false,
                'attribs'    => array('rows' => 4, 'cols' => 20),
                'filters'    => array('StringTrim','StripTags'),
                'decorators' => array(
                    'ViewHelper',
                    'Label',
                    array('HtmlTag', array('tag' => 'div', 'id' => 'edit-album-description-container', 'class' => 'clearfix element-container'))
                ),
            )
        );

        $private = new Zend_Form_Element_Checkbox('private');
        $private->setOptions(
            array(
                'label'      => 'Private',
                'id'         => 'private',
                'name'       => 'private',
                'required'   => true,
                'filters'    => array(),
                'decorators' => array(
                    'ViewHelper',
                    'Label',
                    array('HtmlTag', array('tag' => 'div', 'id' => 'edit-album-private-container', 'class' => 'clearfix element-container')),
                    array('Label', array('class' => 'radio-label'))
                )/*,
                'multiOptions' => array(				    
                    "1" => "Yes",
                    "0" => "No"
                )  */
           /* )
        );

        $visible = new Zend_Form_Element_Checkbox('visible');
        $visible->setOptions(
            array(
                'label'      => 'Visible',
                'id'         => 'visible',
                'name'       => 'visible',
                'required'   => true,
                'filters'    => array(),
                'decorators' => array(
                    'ViewHelper',
                    'Label',
                    array('HtmlTag', array('tag' => 'div', 'id' => 'edit-album-visible-container', 'class' => 'clearfix element-container')),
                    array('Label', array('class' => 'radio-label'))
                )/*,
                'multiOptions' => array(				    
                    "1" => "Yes",
                    "0" => "No"
                )   */
           /* )
        );
        */
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setOptions(
            array(
                'label' => 'Submit',
                'id'         => 'edit-album-submit-button',
                'name'       => 'edit_album_submit_button',
                'attribs'    => array("class" => "button"),
                'decorators' => array('ViewHelper')
            )
        );

        //$this->addElement($album_id);
        $this->addElement($name);
       /* $this->addElement($description);
        $this->addElement($private);
        $this->addElement($visible);*/
        $this->addElement($submit);		
    }
}


