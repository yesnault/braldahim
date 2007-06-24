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
		
		$tab = array(
			'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p',  'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z',
			'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P',  'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
			'\'', '.', ',', 
			'ä', 'â', 'à', 'Ä', 'Â', 'À',
			'é', 'è', 'ê', 'É', 'È', 'Ê',
			'î', 'ï', 'ì', 'Î', 'Ï', 'Ì',
			'ö', 'ô', 'ò', 'Ö', 'Ô', 'Ò',
			'û', 'ü', 'ù', 'Û', 'Ü', 'Ù',
			'ÿ',
			'ç', 'Ç', 'æ', 'Æ', '°', '-',
			'ñ', 'Ñ', 'ã', 'Ã',
			' ', 
			);
    	
		$flag = true;
		$val = null;
		for ($i = 0; $i< strlen($valeur); $i++) {
			if (!in_array($valeur[$i], $tab)) {
				$this->_messages[] = "Le nom du hobbit contient un ou plusieurs caractères invalides";
				foreach ($tab as $t) {
					$val .= $t. " ";
				}	
				$this->_messages[] = "Seuls les caractères suivants sont autorisés : ";
				$this->_messages[] = $val ." (espace)";
				$valid = false;
				break;
			}
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
    
    public function getErrors() {
    	return $this->_messages;
    }
}