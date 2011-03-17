<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Box_EvenementsCommunaute extends Bral_Box_Box {

	function getTitreOnglet() {
		return "Événements de Communauté";
	}

	function getNomInterne() {
		return "box_evenements_communaute";
	}

	function getChargementInBoxes() {
		return false;
	}
	
	function setDisplay($display) {
		$this->view->display = $display;
	}

	function render() {
		Zend_Loader::loadClass("Bral_Util_Communaute");
		if ($this->view->affichageInterne && $this->view->user->rangCommunaute < Bral_Util_Communaute::ID_RANG_NOUVEAU) {
			Zend_Loader::loadClass('EvenementCommunaute');
			Zend_Loader::loadClass('TypeEvenementCommunaute');
			Zend_Loader::loadClass('Bral_Util_ConvertDate');
		
			$this->preparePage();
			$this->prepareRender();
			if ($this->_request->get("box") == "box_evenements_communaute") {
				$this->prepareDetails();
			}
		}
		$this->view->nom_interne = $this->getNomInterne();
		return $this->view->render("interface/evenementscommunaute.phtml");
	}
	
	private function prepareRender() {
		$suivantOk = false;
		$precedentOk = false;
		$tabEvenementsCommunaute = null;
		$tabTypeEvenementsCommunaute = null;
		$evenementCommunauteTable = new EvenementCommunaute();
		$evenementsCommunaute = $evenementCommunauteTable->findByIdCommunaute($this->view->user->id_fk_communaute_braldun, $this->_page, $this->_nbMax, $this->_filtre);
		
		foreach ($evenementsCommunaute as $p) {
			$tabEvenementsCommunaute[] = array(
				"id_evenement" => $p["id_evenement_communaute"],
				"type" => $p["nom_type_evenement_communaute"],
				"date" => Bral_Util_ConvertDate::get_datetime_mysql_datetime('\l\e d/m/y \&\a\g\r\a\v\e; H:i:s',$p["date_evenement_communaute"]),
				"details" => $p["details_evenement_communaute"],
				"details_bot" => $p["details_bot_evenement_communaute"],
			);
		}

		$typeEvenementCommunauteTable = new TypeEvenementCommunaute();
		$typeEvenementsCommunaute = $typeEvenementCommunauteTable->fetchall();

		$tabTypeEvenementsCommunaute[] = array (
			"id_type_evenement" => -1,
			"nom" => "(Tous)"
		);
		foreach ($typeEvenementsCommunaute as $t) {
			$tabTypeEvenementsCommunaute[] = array(
				"id_type_evenement" => $t->id_type_evenement_communaute,
				"nom" => $t->nom_type_evenement_communaute
			);
		}

		if ($this->_page == 1) {
			$precedentOk = false;
		} else {
			$precedentOk = true;
		}

		if (count($tabEvenementsCommunaute) == 0 || count($tabEvenementsCommunaute) < $this->_nbMax) {
			$suivantOk = false;
		} else {
			$suivantOk = true;
		}

		$this->view->precedentOk = $precedentOk;
		$this->view->suivantOk = $suivantOk;
		$this->view->evenements = $tabEvenementsCommunaute;
		$this->view->typeEvenements = $tabTypeEvenementsCommunaute;
		$this->view->nbEvenements = count($this->view->evenements);
		
		$this->view->page = $this->_page;
		$this->view->filtre = $this->_filtre;
		
		unset($precedentOk);
		unset($suivantOk);
	}
	
	private function prepareDetails() {
		$this->view->evenement = null;
		$idEvenement = -1;
		if ($this->_request->get("valeur_5") != null) {
			$idEvenementCommunaute = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_5"));
		} else {
			return;
		}
		
		$trouve = false;
		foreach ($this->view->evenements as $t) {
			if ($t["id_evenement"] == $idEvenementCommunaute) {
				$this->view->evenement = $t;
				$trouve = true;
			}
		}
		
		if ($trouve == false) {
			throw new Zend_Exception(get_class($this)." Evenement invalide:".$idEvenement);
		}
	}
	
	private function preparePage() {
		$this->_page = 1;
		if (($this->_request->get("box") == "box_evenements_communaute") && ($this->_request->get("valeur_1") == "f")) {
			$this->_filtre =  Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_2"));
		} else if (($this->_request->get("box") == "box_evenements_communaute") && ($this->_request->get("valeur_1") == "p")) { 
			$this->_page =  Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_3")) - 1;
			$this->_filtre =  Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_4"));
		} else if (($this->_request->get("box") == "box_evenements_communaute") && ($this->_request->get("valeur_1") == "s")) {
			$this->_page =  Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_3")) + 1;
			$this->_filtre =  Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_4"));
		} else if (($this->_request->get("box") == "box_evenements_communaute") && ($this->_request->get("valeur_1") == "d")) {
			$this->_page =  Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_3"));
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
