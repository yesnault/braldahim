<?php
require_once 'Zend/Validate/Interface.php';

class Bral_Validate_Messagerie_Destinataires implements Zend_Validate_Interface {
	protected $_messages = array();
	
	public function __construct($estObligatoire) {
		$this->estObligatoire = $estObligatoire;
	}
	
	public function isValid($valeur) {
		$this->_messages = array();
		$valid = true;

		if ((strlen($valeur) < 1) && ($this->estObligatoire === true)) {
			$this->_messages[] = "Ce champ est obligatoire";
			$valid = false;
		}

		if (strlen($valeur) > 1000) {
			$this->_messages[] = "Ce champ doit contenir au maximum 100 caractères";
			$valid = false;
		}

		if ($valid) {
			if (!preg_match('`^([[:digit:]]+(,|[[:space:]])*)+$`',$valeur)) {
				$this->_messages[] = "Ce champ contient des caractères invalides";
				$valid = false;
			}
		}
		if ($valid) {
			
			$hobbitTable = new Hobbit();
			$idDestinatairesTab = split(',', $valeur);
			foreach ($idDestinatairesTab as $id) {
				$r = $hobbitTable->findById(trim($valeur));
				if (count($r) == 0) {
					$this->_messages[] = "Le hobbit n°$id est inconnu";
					$valid = false;
				}
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