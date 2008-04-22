<?php

class Bral_Box_Metier extends Bral_Box_Box {

	function __construct($request, $view, $interne) {
		Zend_Loader::loadClass("HobbitsMetiers");

		$this->_request = $request;
		$this->view = $view;
		$this->view->affichageInterne = $interne;
	}

	function getTitreOnglet() {
		return "M&eacute;tier";
	}

	function getNomInterne() {
		return "box_metier";
	}

	function getChargementInBoxes() {
		return false;
	}
	
	function setDisplay($display) {
		$this->view->display = $display;
	}

	function render() {
		$hobbitsMetiersTable = new HobbitsMetiers();
		$hobbitsMetierRowset = $hobbitsMetiersTable->findMetiersByHobbitId($this->view->user->id_hobbit);
		$tabMetiers = null;
		$tabMetierCourant = null;
		$possedeMetier = false;

		foreach($hobbitsMetierRowset as $m) {
			$possedeMetier = true;
			
			if ($this->view->user->sexe_hobbit == 'feminin') {
				$nom_metier = $m["nom_feminin_metier"];
			} else {
				$nom_metier = $m["nom_masculin_metier"];
			}
			
			$t = array("id_metier" => $m["id_metier"],
			"nom" => $nom_metier,
			"nom_systeme" => $m["nom_systeme_metier"],
			"est_actif" => $m["est_actif_hmetier"],
			"date_apprentissage" => Bral_Util_ConvertDate::get_date_mysql_datetime("d/m/Y", $m["date_apprentissage_hmetier"]),
			"description" => $m["description_metier"]);
			
			if ($m["est_actif_hmetier"] == "non") {
				$tabMetiers[] = $t;
			}

			if ($m["est_actif_hmetier"] == "oui") {
				$tabMetierCourant = $t;
			}
		}

		$this->view->tabMetierCourant = $tabMetierCourant;
		$this->view->tabMetiers = $tabMetiers;
		$this->view->possedeMetier = $possedeMetier;
		$this->view->nom_interne = $this->getNomInterne();
		return $this->view->render("interface/metier.phtml");
	}
}
