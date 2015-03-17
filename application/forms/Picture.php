<?php
class Application_Form_Picture extends Zend_Form
{

    public function __construct() {

        parent::__construct();

        $view = $this->getView();

        $this->setName("form_edit_picture");
        $this->setAttrib("id", "form-edit-picture");
        $this->setAction($view->baseUrl('picture/edit/'));
        $this->setEnctype("application/x-www-form-urlencoded");
        $this->setMethod("post");
        $this->setDecorators(array('FormElements', 'Form'));

        $picture_id = new Zend_Form_Element_Hidden('id');
        $picture_id->setOptions(
            array(
                'required'   => true,
                'id'         => 'id',
                'name'       => 'id',
                'decorators' => array('ViewHelper')
            )
        );

        $album_id = new Zend_Form_Element_Hidden('album_id');
        $album_id->setOptions(
            array(
                'required'   => true,
                'id'         => 'album',
                'name'       => 'album',
                'decorators' => array('ViewHelper')
            )
        );

        $title = new Zend_Form_Element_Text('title');
        $title->setOptions(
            array(
                'label'      => 'Title',
                'id'         => 'title',
                'name'       => 'title',
                'required'   => false,
                'filters'    => array('StringTrim','StripTags'),
                'decorators' => array(
                    'ViewHelper',
                    'Label',
                    array('HtmlTag', array('tag' => 'div', 'id' => 'edit-picture-title-container', 'class' => 'clearfix element-container'))
                ),
            )
        );

        $description = new Zend_Form_Element_Textarea('description');
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
                    array('HtmlTag', array('tag' => 'div', 'id' => 'edit-picture-description-container', 'class' => 'clearfix element-container'))
                ),
            )
        );

        /*$private = new Zend_Form_Element_Checkbox('private');
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
                    array('HtmlTag', array('tag' => 'div', 'id' => 'edit-picture-private-container', 'class' => 'clearfix element-container'))                	,
                    array('Label', array('class' => 'radio-label'))
                )         
            )
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
                    array('HtmlTag', array('tag' => 'div', 'id' => 'edit-picture-visible-container', 'class' => 'clearfix element-container')),
                    array('Label', array('class' => 'radio-label'))
                )              
            )
        );*/

            /*
        $private = new Zend_Form_Element_Radio('private');
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
                    array('HtmlTag', array('tag' => 'div', 'id' => 'edit-picture-visible-container', 'class' => 'clearfix element-container'))                	,
                    array('Label', array('class' => 'radio-label'))
                ),
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
                'decorators' => array(
                    'ViewHelper',
                    'Label',
                    array('HtmlTag', array('tag' => 'div', 'id' => 'edit-picture-visible-container', 'class' => 'clearfix element-container')),
                    array('Label', array('class' => 'radio-label'))
                ),
                'multiOptions' => array(				    
                    "1" => "Yes",
                    "0" => "No"
                )
            )
        );
        */
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setOptions(
            array(
                'label' => 'Submit',
                'id'         => 'edit-picture-submit-button',
                'name'       => 'edit_picture_submit_button',
                'attribs'    => array("class" => "button"),
                'decorators' => array('ViewHelper')
            )
        );

        $this->addElement($picture_id);
        $this->addElement($album_id);
        $this->addElement($title);
        $this->addElement($description);
        //$this->addElement($private);
        //$this->addElement($visible);
      //  $this->addElement($submit);		
    }
}
