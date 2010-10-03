<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
require_once 'Zend/Validate/Interface.php';

class Bral_Validate_Messagerie_Contacts implements Zend_Validate_Interface {
	protected $_messages = array();
	
	public function __construct($estObligatoire, $idFkJosUsersBraldun) {
		$this->estObligatoire = $estObligatoire;
		$this->idFkJosUsersBraldun = $idFkJosUsersBraldun;
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

		if (mb_strlen($valeur) > 30) {
			$this->_messages[] = "Trop de Contacts destinataires";
			$valid = false;
		}
		
		if ($valid) {
			if (!preg_match_all('`^([[:digit:]]+(,|[[:space:]])*)+$`',$valeur, $matches)) {
				$this->_messages[] = "Ce champ contient des caractères invalides";
				$valid = false;
			}
		}
		
		Zend_Loader::loadClass('MessagerieContacts');
		$messagerieContactsTable = new MessagerieContacts();
		$idContactsTab = preg_split("/,/", $valeur);
		$messagerieContacts = $messagerieContactsTable->findByIdsList($idContactsTab, $this->idFkJosUsersBraldun);
		
		if ($valid) {
			foreach ($idContactsTab as $id) {
				$trouve = false;
				
				foreach($messagerieContacts as $j) {
					if ($j["id"] == $id) {
						$trouve = true;
					}
				}
				if ($trouve == false) {
					$this->_messages[] = "Ce contact est inconnu ". $j["name"];
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