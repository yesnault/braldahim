<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Communaute_Coffre extends Bral_Communaute_Communaute {

	function __construct($request, $view, $interne) {
		$this->_request = $request;
		$this->view = $view;
		$this->view->affichageInterne = $interne;

	}

	function getNomInterne() {
		return "box_communaute_action";
	}

	function setDisplay($display) {
		$this->view->display = $display;
	}

	function render() {
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
		Bral_Util_Coffre::prepareData($tabMetiers, $this->view, $this->view->user->id_braldun, null);

		$this->view->tabMetiers = $tabMetiers;
		$this->view->tabBraldunMetiers = null;

		$this->view->estElementsEtal = false;
		$this->view->estElementsEtalAchat = false;
		$this->view->estElementsAchat = false;

		$this->view->pocheNom = "Tiroir";
		$this->view->pocheNomSysteme = "Coffre";
		$this->view->nb_castars = $this->view->coffre["nb_castar"];

		$this->view->nom_interne = $this->getNomInterne();
		return $this->view->render("interface/communaute/coffre.phtml");
	}
}
