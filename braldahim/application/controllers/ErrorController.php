<?php


class ErrorController extends Zend_Controller_Action {
	
	public function init() {
		$this->initView();
	}
	
	public function errorAction() {
		$errors = $this->_getParam('error_handler');

		switch ($errors->type) {
			case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
			case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
				// 404 error -- controller or action not found
				$this->getResponse()->setRawHeader('HTTP/1.1 404 Not Found');

				$content =<<<EOH
<h1>Erreur !</h1>
<p>Page introuvable.</p>
EOH;
				echo $content;
				break;
			default:
				$errors = $this->_getParam('error_handler');
				$exception = $errors->exception;
		 
		 		Bral_Util_Exception::traite("type:".$errors->type." msg:".$exception->getMessage()." in ".$exception->getFile().":".$exception->getLine().PHP_EOL." StackTrace:".PHP_EOL.$exception->getTraceAsString(), true);
		}

		// Vide le contenu de la réponse
		$this->getResponse()->clearBody();
	}
}
