<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id$
 * $Author$
 * $LastChangedDate$
 * $LastChangedRevision$
 * $LastChangedBy$
 */
require_once 'Zend/Validate/Interface.php';

class Bral_Validate_Inscription_PrenomBraldun implements Zend_Validate_Interface {
    protected $_messages = array();

    public function isValid($valeur) {
    
        $this->_messages = array();
		$valid = true;
		
		if (mb_strlen($valeur) < 5) {
			$this->_messages[] = "Le pr&eacute;nom du braldun doit contenir plus de 5 caract&egrave;res";
			$valid = false;
		}
		
    	if (mb_strlen($valeur) > 15) {
			$this->_messages[] = "Le pr&eacute;nom du braldun doit contenir au maximum 15 caract&egrave;res";
			$valid = false;
    	}
		
		$flag = true;
		$val = null;
		for ($i = 0; $i< mb_strlen($valeur); $i++) {
			$trouve = Bral_Util_String::isCaractereValid(mb_substr($valeur, $i, 1));
			if ($trouve == false) {
				$this->_messages[] = "Le nom du braldun contient un ou plusieurs caract&egrave;res invalides : ".mb_substr($valeur, $i, 1);
				$tab = Bral_Util_String::getTabCaractereValid();
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
    	
		if ($nom->estPrenomAutorise($valeur) == false) {
			$this->_messages[] = "Ce pr&eacute;nom est interdit.";
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