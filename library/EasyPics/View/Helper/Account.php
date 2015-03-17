<?php
class EasyPics_View_Helper_Account extends Zend_View_Helper_HtmlElement
{

    protected $_firstname = "";


    protected $_lastname = "";

    protected $_partial;

    public function Account($partial = 'includes/account.phtml'){
    
        $this->_partial = $partial;
    
        $auth = Zend_Auth::getInstance();

        if ($auth->hasIdentity()) {

            $user = $auth->getStorage()->read();
            $this->_firstname = $user->firstname;
            $this->_lastname = $user->lastname;
        }
        return $this;
    }

    public function render() {
        $params = array(
            'firstname'   => $this->_firstname,
            'lastname'   => $this->_lastname
        );		
        return $this->view->partial($this->_partial, null, $params);
    }

    public function direct()
    {
        return $this->Account();
    }
}
