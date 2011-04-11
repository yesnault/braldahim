<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Communaute_Coffre extends Bral_Communaute_Communaute {

	function prepareCommun() {}
	function prepareFormulaire() {}
	function prepareResultat() {}
	function getListBoxRefresh() {}

	function getNomInterne() {
		return "box_communaute_action";
	}

	function getTitre() {
		return "";
	}

	function setDisplay($display) {
		$this->view->display = $display;
	}

	function render() {

		Zend_Loader::loadClass('Bral_Util_Lot');
		Zend_Loader::loadClass('Bral_Util_Poids');
		Zend_Loader::loadClass('Bral_Util_String');
		Zend_Loader::loadClass('Bral_Util_Communaute');

		if ($this->view->user->rangCommunaute == Bral_Util_Communaute::ID_RANG_NOUVEAU) {
			throw new Zend_Exception("Vous n'avez pas accès au coffre, vous êtes nouveau");
		}

		$this->view->lots = Bral_Util_Lot::getLotsByIdCommunaute($this->view->user->id_fk_communaute_braldun, false);

		$estSurHall = Bral_Util_Communaute::estSurHall($this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun, $this->view->user->id_fk_communaute_braldun);

		Zend_Loader::loadClass("Metier");
		$metiersTable = new Metier();
		$metiersRowset = $metiersTable->fetchall(null, "nom_masculin_metier");
		$metiersRowset = $metiersRowset->toArray();
		$tabMetiers = null;

		foreach($metiersRowset as $m) {
			$tabMetiers[$m["nom_systeme_metier"]] = array(
				"id_metier" => $m["id_metier"],
				"nom" => $m["nom_masculin_metier"],
				"nom_systeme" => $m["nom_systeme_metier"],
				"a_afficher" => true,
			);
		}

		Zend_Loader::loadClass("Bral_Util_Coffre");
		// passage par reference de tabMetiers et this->view
		Bral_Util_Coffre::prepareData($tabMetiers, $this->view, null, $this->view->user->id_fk_communaute_braldun);

		
		$this->view->estSurHall = $estSurHall;
		
		$this->view->tabMetiers = $tabMetiers;
		$this->view->tabBraldunMetiers = null;

		$this->view->estElementsEtal = false;
		$this->view->estElementsEtalAchat = false;
		$this->view->estElementsAchat = false;

		$this->view->pocheNom = "Tiroir";
		$this->view->pocheNomSysteme = "CoffreCommunaute";
		$this->view->nb_castars = $this->view->coffre["nb_castar"];

		$this->view->nom_interne = $this->getNomInterne();
		return $this->view->render("interface/communaute/coffre.phtml");
	}
}
