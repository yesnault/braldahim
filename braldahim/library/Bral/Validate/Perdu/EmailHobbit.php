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

class Bral_Validate_Perdu_EmailHobbit implements Zend_Validate_Interface {
    protected $_messages = array();

    public function isValid($valeur) {
        $this->_messages = array();
		$valid = true;
		
		if (mb_strlen($valeur) < 5) {
			$this->_messages[] = "L'email du hobbit doit contenir plus de 5 caract�res";
			$valid = false;
		}
		
    	if (mb_strlen($valeur) > 100) {
			$this->_messages[] = "L'email doit doit contenir au maximum 100 caract�res";
			$valid = false;
    	}
		
    	if ($valid) {
			$hobbitTable = new Hobbit();
			$r = $hobbitTable->findByEmail($valeur);
			if (count($r) == 0) {
				$this->_messages[] = "Cette adresse mail est inconnue";
				$valid = false;
			}
		}
		
		if ($valid) {
			$validateur = new Zend_Validate_EmailAddress();
			if (!$validateur->isValid($valeur)) {
				print_r($validateur->getMessages());
				$this->_messages[] = "Cette adresse est invalide:";
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