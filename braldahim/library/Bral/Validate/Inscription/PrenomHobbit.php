<?php
require_once 'Zend/Validate/Interface.php';

class Bral_Validate_Inscription_PrenomHobbit implements Zend_Validate_Interface {
    protected $_messages = array();

    public function isValid($valeur) {
    
        $this->_messages = array();
		$valid = true;
		
		if (strlen($valeur) < 5) {
			$this->_messages[] = "Le pr&eacute;nom du hobbit doit contenir plus de 5 caract&egrave;res";
			$valid = false;
		}
		
    	if (strlen($valeur) > 15) {
			$this->_messages[] = "Le pr&eacute;nom du hobbit doit contenir au maximum 15 caract&egrave;res";
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
			$trouve = false;
			foreach ($tab as $v) {
				if ($v == substr($valeur, $i, 1)) {
					$trouve = true;
				}
				
			}
			if ($trouve == false) {
				$this->_messages[] = "Le nom du hobbit contient un ou plusieurs caract&egrave;res invalides".substr($valeur, $i, 1);
				foreach ($tab as $t) {
					$val .= $t. " ";
				}	
				$this->_messages[] = "Seuls les caract&egrave;res suivants sont autoris&eacute;s : ";
				$this->_messages[] = $val ." (espace)";
				$valid = false;
				break;
			}
		}
		
    	Zend_Loader::loadClass('Bral_Util_Nom');
    	$nom = new Bral_Util_Nom();
    	
    	if ($nom->estValidPrenom($valeur) == false) {
			$this->_messages[] = "Ce pr&eacute;nom est d&eacute;j&agrave; trop utilis&eacute;...";
			$valid = false;
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