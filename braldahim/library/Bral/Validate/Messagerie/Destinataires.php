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

		if ((mb_strlen($valeur) < 1) && ($this->estObligatoire === true)) {
			$this->_messages[] = "Ce champ est obligatoire";
			$valid = false;
		}
		
		// si le champ est vide, mais qu'il n'est pas obligatoire, on sort tout de suite
		if ((mb_strlen($valeur) < 1) && ($this->estObligatoire === false)) {
			return true;
		}

		if (mb_strlen($valeur) > 1000) {
			$this->_messages[] = "Trop de Hobbit destinataires";
			$valid = false;
		}
		
		//  TODO A MODIFIER avec split
		if ($valid) {
			if (!preg_match_all('`^([[:digit:]]+(,|[[:space:]])*)+$`',$valeur, $matches)) {
				$this->_messages[] = "Ce champ contient des caract�res invalides";
				$valid = false;
			}
		}
		
		if ($valid) {
			$hobbitTable = new Hobbit();
			foreach ($matches[0] as $id) {
				$r = $hobbitTable->findByIdFkJosUsers(trim($id));
				if ($r == null || count($r) == 0) {
					$this->_messages[] = "Le hobbit est inconnu";
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