<?php

class IndexController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        // action body
    }

    public function menuAction()
    {
        // action body
    	$this->_helper->viewRenderer->setNoRender();
    	$content = $this->view->render("index/menu.phtml");
    	echo preg_replace("/(\r\n|\n|\r)/", " ", "document.write('$content');");
    }

    public function footerAction()
    {
        // action body
        $this->_helper->viewRenderer->setNoRender();
    	$content = addslashes($this->view->render("index/footer.phtml"));
    	echo preg_replace("/(\r\n|\n|\r)/", " ", "document.write('$content');");
    }


}





