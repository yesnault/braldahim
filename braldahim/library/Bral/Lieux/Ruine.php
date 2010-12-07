<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Lieux_Ruine extends Bral_Lieux_Lieu {

	function prepareCommun() {

		$this->view->transformerOk = false;

		if ($this->view->user->id_fk_communaute_braldun == null || $this->view->user->id_fk_rang_communaute_braldun == null) {
			return;
		}

		Zend_Loader::loadClass("RangCommunaute");
		$rangCommunauteTable = new RangCommunaute();
		$rang = $rangCommunauteTable->findRangCreateur($this->view->user->id_fk_communaute_braldun);

		if ($this->view->user->id_fk_rang_communaute_braldun == $rang["id_rang_communaute"]) { // rang 1 : Gestionnaire
			$this->view->transformerOk = true;
		}

	}

	function prepareFormulaire() {

	}

	function prepareResultat() {
		
		if ($this->view->transformerOk == false) {
			throw new Zend_Exception("Erreur Bral_Lieux_Ruine, transformer KO");
		}
		
		$this->view->user->balance_faim_braldun = $this->view->user->balance_faim_braldun - 6;
		$this->majBraldun();
	}

	function getListBoxRefresh() {
		return $this->constructListBoxRefresh(array("box_vue", "box_lieu"));
	}
}