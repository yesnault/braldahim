<?php

class Bral_Messagerie_Message {

	function __construct($request, $view, $action) {
		Zend_Loader::loadClass("Message");
		
		$this->view = $view;
		$this->request = $request;
		$this->action = $action;
	}
	
	function render() {
		switch($this->action) {
			case "ask":
			case "do":
				return $this->view->render("messagerie/message.phtml");
				break;
			default:
				throw new Zend_Exception(get_class($this)."::action invalide :".$this->action);
		}
	}
}