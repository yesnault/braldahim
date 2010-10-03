<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
require_once 'Zend/Validate/Interface.php';

class Bral_Validate_Inscription_EmailBraldun implements Zend_Validate_Interface {
	protected $_messages = array();

	public function isValid($valeur, $estEnProduction = false) {
		$this->_messages = array();
		$valid = true;

		if (mb_strlen($valeur) < 5) {
			$this->_messages[] = "L'email du braldun doit contenir plus de 5 caract&egrave;res";
			$valid = false;
		}

		if (mb_strlen($valeur) > 100) {
			$this->_messages[] = "L'email doit doit contenir au maximum 100 caract&egrave;res";
			$valid = false;
		}

		if ($valid) {
			$braldunTable = new Braldun();
			$r = $braldunTable->findByEmail($valeur);
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
			Zend_Loader::loadClass("Jetable");
			$jetableTable = new Jetable();
			preg_match('@^.*\@([^/]+)\..*$@i', $valeur, $matches);
			$nomDomaine = $matches[1].'%';
			$nb = $jetableTable->countByNom($nomDomaine);
			if ($nb > 0) {
				$this->_messages[] = "Cette adresse mail est invalide";
				$valid = false;
			}
		}

		if ($valid && !$estEnProduction) {
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