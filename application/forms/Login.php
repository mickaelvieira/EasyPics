<?php
class Application_Form_Login extends Zend_Form
{

    public function __construct()
    {

        parent::__construct();

        $view = $this->getView();

        $this->setName("form_login");
        $this->setAttrib("id", "form-login");
        $this->setAction($view->baseUrl('index/login/'));
        $this->setEnctype("application/x-www-form-urlencoded");
        $this->setMethod("post");
    //	$this->setDecorators(array('FormElements', 'Form'));


        $this->setDecorators(array(
            array('ViewScript', array('viewScript' => 'index/forms/login.phtml'))
        ));

        $login = new Zend_Form_Element_Text('login');
        $login->setOptions(
            array(
                'label'      => 'Login',
                'id'         => 'login',
                'name'       => 'login',
                'required'   => true,
                'filters'    => array('StringTrim','StripTags'),
                'decorators' => array(
                    'ViewHelper'/*,
                    'Label',
                    array('HtmlTag', array('tag' => 'div', 'class' => 'clearfix element-container')) */
                )
            )
        );
        //$login;

        $password = new Zend_Form_Element_Password('password');
        $password->setOptions(
            array(
                'label'		=> 'password',
                'id'        => 'password',
                'name'      => 'password',
                'required'	=> true,
                'filters'	=> array('StringTrim','StripTags'),
                'decorators' => array(
                    'ViewHelper'/*,
                    'Label',
                    array('HtmlTag', array('tag' => 'div', 'class' => 'clearfix element-container'))  */
                )
            )
        );
 
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setOptions(
            array(
                'label' => 'Submit',
                'attribs'    => array("class" => "btn"),
                'decorators' => array(
                    'ViewHelper',
                    array('HtmlTag', array('tag' => 'div', 'class' => 'clearfix element-container'))
                )
            )
        );
 
        $this->addElement($login);
        $this->addElement($password);
        $this->addElement($submit);
    }
}
