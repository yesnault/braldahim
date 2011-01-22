<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
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

				$content ="<h1>Erreur !</h1><p>Page introuvable.</p>";
				echo $content;
				break;
			default:
				$this->getResponse()->setHttpResponseCode(500);
				$errors = $this->_getParam('error_handler');
				$exception = $errors->exception;
				$texte = "Type: ".$errors->type.PHP_EOL;
				$texte .= "Fichier: ".$exception->getFile().":".$exception->getLine().PHP_EOL;
				$texte .= "Message: ".$exception->getMessage().PHP_EOL.PHP_EOL;
				$texte .= "StackTrace:".PHP_EOL.$exception->getTraceAsString().PHP_EOL.PHP_EOL;
				$texte .= "Params:".var_export($errors->request->getParams(), true);
				echo $texte;
				break;
		}

		// conditionally display exceptions
		if ($this->getInvokeArg('displayExceptions') == true) {
			$this->view->exception = $errors->exception;
		}

		$this->view->request = $errors->request;

		// Vide le contenu de la réponse
		$this->getResponse()->clearBody();
		$this->render();
	}
}
