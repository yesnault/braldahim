<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Lieux_Lieumythique extends Bral_Lieux_Lieu {

	private $_distinction = null;

	function prepareCommun() {
		Zend_Loader::loadClass("BraldunsDistinction");
		$braldunsDistinctionTable = new BraldunsDistinction();
		$distinction = $braldunsDistinctionTable->findDistinctionsByBraldunIdAndIdFkLieuDistinction($this->view->user->id_braldun, $this->view->idLieu);
		if (count($distinction) >= 1) {
			$this->view->utilisationPossible = false;
		} else {
			Zend_Loader::loadClass("TypeDistinction");
			$typeDistinctionTable = new TypeDistinction();
			$distinction = $typeDistinctionTable->findByIdFkTypeLieu($this->view->idLieu);
			if ($distinction == null) {
				throw new Zend_Exception("Lieu Mythique invalide:".$this->view->idLieu);
			} else {
				$this->_distinction = $distinction;
				$this->view->nom_distinction = $distinction->nom_type_distinction;
			}
			$this->view->utilisationPossible = true;
		}
	}

	function prepareFormulaire() {
	}

	function prepareResultat() {
		Zend_Loader::loadClass("Bral_Util_Distinction");
		Bral_Util_Distinction::ajouterDistinction($this->view->user->id_braldun, $this->_distinction->id_type_distinction, $this->_distinction->nom_type_distinction);

		$this->majBraldun();
	}

	function getListBoxRefresh() {
		return $this->constructListBoxRefresh(array("box_titres"));
	}

}