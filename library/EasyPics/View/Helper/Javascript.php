<?php
/**
 * Description of Menu
 *
 * @author mike
 */
class EasyPics_View_Helper_Javascript extends Zend_View_Helper_Abstract
{


    protected $_paths = array();

    protected $_scripts;


    public function Javascript()
    {

        $this->_scripts = $this->view->headScript();
        $this->_scripts->setAllowArbitraryAttributes(true);
        foreach ($this->_paths as $k => $path) {
            $this->_scripts->appendFile($this->view->baseUrl($path, $type = 'text/javascript', $attrs = array()));
        }
        return $this;
    }

    public function setPaths($paths = array())
    {
        $this->_paths = $paths;
    }

    public function render()
    {
        return $this->_scripts;
    }

    public function direct() {
        return $this->Javascript();
    }
}
?>
