<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Communaute_Infirmerie extends Bral_Communaute_Communaute {

	function getTitreOnglet() {}
	function setDisplay($display) {}

	function getTitre() {
		return "Infirmerie";
	}

	function getNomInterne() {
		return "box_action";
	}

	function prepareCommun() {
		Zend_Loader::loadClass("Bral_Util_Communaute");
		Zend_Loader::loadClass("TypeLieu");

		if (!Bral_Util_Communaute::possedeUnHall($this->view->user->id_fk_communaute_braldun)) {
			throw new Zend_Exception("Bral_Communaute_Infirmerie :: Hall invalide idC:".$this->view->user->id_fk_communaute_braldun);
		}
		if (Bral_Util_Communaute::getNiveauDuLieu($this->view->user->id_fk_communaute_braldun, TypeLieu::ID_TYPE_INFIRMERIE) < Bral_Util_Communaute::NIVEAU_INFIRMERIE_REVENIR) {
			throw new Zend_Exception("Bral_Communaute_Infirmerie::Erreur Infirmerie, niveau invalide");
		}
	}

	function prepareFormulaire() {
	}

	function prepareResultat() {
		if ($this->_request->get("valeur_1")."" != "oui" && $this->_request->get("valeur_1")."" != "non") {
			throw new Zend_Exception("Bral_Communaute_Infirmerie :: Choix invalide : ".$this->_request->get("valeur_1"));
		} else {
			$choix = $this->_request->get("valeur_1");
		}

		$lieu = Bral_Util_Communaute::recupereLieu($this->view->user->id_fk_communaute_braldun, TypeLieu::ID_TYPE_INFIRMERIE, Bral_Util_Communaute::NIVEAU_INFIRMERIE_REVENIR);

		if ($choix == "oui" && $lieu != null) {
			$this->view->user->id_fk_lieu_resurrection_braldun = $lieu["id_lieu"];
		} else {
			$this->view->user->id_fk_lieu_resurrection_braldun = null;
			$lieu = null;
		}

		$this->majBraldun();
		$this->view->lieu = $lieu;
	}

	function getListBoxRefresh() {
		return array("box_communaute_batiments");
	}

}