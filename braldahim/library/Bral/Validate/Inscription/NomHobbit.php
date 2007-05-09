<?php
require_once 'Zend/Validate/Interface.php';

class Bral_Validate_Inscription_NomHobbit implements Zend_Validate_Interface {
    protected $_messages = array();

    public function isValid($valeur) {
        $this->_messages = array();
		$valid = true;
		
		if (strlen($valeur) < 5) {
			$this->_messages[] = "Le nom du hobbit doit contenir plus de 5 caractères";
			$valid = false;
		}
		
    	if (strlen($valeur) > 20) {
			$this->_messages[] = "Le nom du hobbit doit contenir au maximum 20 caractères";
			$valid = false;
    	}
		
		if ($valid) {
			$hobbitTable = new Hobbit();
			$r = $hobbitTable->findByNom($valeur);
			if (count($r) > 0) {
				$this->_messages[] = "Ce nom de hobbit est déjà utilisé";
				$valid = false;
			}
		}
		return $valid;
    }

    public function getMessages(){
        return $this->_messages;
    }
}