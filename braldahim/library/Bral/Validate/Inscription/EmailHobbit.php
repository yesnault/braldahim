<?php
require_once 'Zend/Validate/Interface.php';

class Bral_Validate_Inscription_EmailHobbit implements Zend_Validate_Interface {
    protected $_messages = array();

    public function isValid($valeur) {
        $this->_messages = array();
		$valid = true;

		if (mb_strlen($valeur) < 5) {
			$this->_messages[] = "L'email du hobbit doit contenir plus de 5 caract&egrave;res";
			$valid = false;
		}
		
    	if (mb_strlen($valeur) > 100) {
			$this->_messages[] = "L'email doit doit contenir au maximum 100 caract&egrave;res";
			$valid = false;
    	}
		
    	if ($valid) {
			$hobbitTable = new Hobbit();
			$r = $hobbitTable->findByEmail($valeur);
			if (count($r) > 0) {
				$this->_messages[] = "Cette adresse mail d&eacute;j&agrave; utilis&eacute;e";
				$valid = false;
			}
		}
		
		if ($valid) {
			$validateur = new Zend_Validate_EmailAddress();
			if (!$validateur->isValid($valeur)) {
				$tab = $validateur->getMessages();
				$this->_messages[] = "Cette adresse est invalide: ".$tab["emailAddressInvalidHostname"];
				$valid = false;
			}
		}
		
    	if ($valid) {
    		Zend_Loader::loadClass("Testeur");
    		$testeurTable = new Testeur();
			$r = $testeurTable->findByEmail($valeur);
			if (count($r) == 0) {
				$this->_messages[] = "Cette adresse mail est invalide en Beta Test";
				$valid = false;
			}
		}

		return $valid;
    }

    public function getMessages(){
        return $this->_messages;
    }

    public function getErrors() {
    	return $this->_messages;
    }
}