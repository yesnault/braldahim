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
class Bral_Voir_Monstre {

	function __construct($request, $view) {
		Zend_Loader::loadClass("Evenement");
		Zend_Loader::loadClass("TypeEvenement");
		Zend_Loader::loadClass("Monstre");

		$this->_request = $request;
		$this->view = $view;
	}

	function getNomInterne() {
		return "box_voir_monstre_inner";
	}

	function setDisplay($display) {
		$this->view->display = $display;
	}

	function render() {
		$this->view->connu = false;
		$this->view->monstre = null;
		
		$monstreTable = new Monstre();
		$monstreRowset = $monstreTable->findById(Bral_Util_Controle::getValeurIntVerif($this->_request->get("monstre")));
		if ($monstreRowset != null) {
			$this->view->monstre = $monstreRowset;
			$this->view->connu = true;
		} else {
			$monstre = null;
		}
		
		if ($this->_request->get("menu") == "evenements" && $this->view->connu != null) {
			return $this->renderEvenements();
		} else { 
			return $this->view->render("voir/monstre.phtml");
		}
	}
	
	function renderEvenements() {
		$this->preparePage();
		
		$suivantOk = false;
		$precedentOk = false;
		$tabEvenements = null;
		$tabTypeEvenements = null;
		$evenementTable = new Evenement();
		$evenements = $evenementTable->findByIdMonstre($this->view->monstre["id_monstre"], $this->_page, $this->_nbMax, $this->_filtre);

		foreach ($evenements as $p) {
			$tabEvenements[] = array(
			"type" => $p["nom_type_evenement"],
			"date" => Bral_Util_ConvertDate::get_datetime_mysql_datetime('\l\e d/m/y \&\a\g\r\a\v\e; H:i:s',$p["date_evenement"]),
			"details" => $p["details_evenement"],
			);
		}

		$typeEvenementTable = new TypeEvenement();
		$typeEvenements = $typeEvenementTable->fetchall();

		$tabTypeEvenements[] = array(
			"id_type_evenement" => -1,
			"nom" => "(Tous)"
		);
		foreach ($typeEvenements as $t) {
			$tabTypeEvenements[] = array(
			"id_type_evenement" => $t->id_type_evenement,
			"nom" => $t->nom_type_evenement
			);
		}

		if ($this->_page == 1) {
			$precedentOk = false;
		} else {
			$precedentOk = true;
		}

		if (count($tabEvenements) == 0) {
			$suivantOk = false;
		} else {
			$suivantOk = true;
		}

		$this->view->precedentOk = $precedentOk;
		$this->view->suivantOk = $suivantOk;
		$this->view->evenements = $tabEvenements;
		$this->view->typeEvenements = $tabTypeEvenements;
		$this->view->nbEvenements = count($this->view->evenements);
		
		$this->view->nom_interne = $this->getNomInterne();
		$this->view->page = $this->_page;
		$this->view->filtre = $this->_filtre;
		return $this->view->render("voir/monstre/evenements.phtml");
	}
	
	private function preparePage() {
		$this->_page = 1;
		if (($this->_request->get("caction") == "ask_voir_monstre") && ($this->_request->get("valeur_1") == "f")) {
			$this->_filtre =  Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_2"));
		} else if (($this->_request->get("caction") == "ask_voir_monstre") && ($this->_request->get("valeur_1") == "p")) { // si le joueur a clique sur une icone
			$this->_page =  Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_3")) - 1;
			$this->_filtre =  Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_4"));
		} else if (($this->_request->get("caction") == "ask_voir_monstre") && ($this->_request->get("valeur_1") == "s")) {
			$this->_page =  Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_3")) + 1;
			$this->_filtre =  Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_4"));
		} else {
			$this->_page = 1;
			$this->_filtre = -1;
		}

		if ($this->_page < 1) {
			$this->_page = 1;
		}
		$this->_nbMax = $this->view->config->game->evenements->nb_affiche;
	}
}
