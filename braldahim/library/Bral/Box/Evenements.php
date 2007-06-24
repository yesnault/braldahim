<?php

class Bral_Box_Evenements {

	function __construct($request, $view, $interne) {
		Zend_Loader::loadClass('Evenement');
		Zend_Loader::loadClass('TypeEvenement');
		Zend_Loader::loadClass('Bral_Util_ConvertDate');
		$this->_request = $request;
		$this->view = $view;
		$this->view->affichageInterne = $interne;

		$this->preparePage();
	}

	function getTitreOnglet() {
		return "&Eacute;v&egrave;nements";
	}

	function getNomInterne() {
		return "box_evenements";
	}

	function setDisplay($display) {
		$this->view->display = $display;
	}

	function render() {
		$suivantOk = false;
		$precedentOk = false;
		$tabEvenements = null;
		$tabTypeEvenements = null;
		$evenementTable = new Evenement();
		$evenements = $evenementTable->findByIdHobbit($this->view->user->id_hobbit, $this->_page, $this->_nbMax, $this->_filtre);

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
		return $this->view->render("interface/evenements.phtml");
	}

	private function preparePage() {
		$this->_page = 1;
		if (($this->_request->get("caction") == "box_evenements") && ($this->_request->get("valeur_1") == "f")) {
			$this->_filtre = $this->getValeurVerif($this->_request->get("valeur_2"));
		} else if (($this->_request->get("caction") == "box_evenements") && ($this->_request->get("valeur_1") == "p")) { // si le joueur a clique sur une icone
			$this->_page = $this->getValeurVerif($this->_request->get("valeur_3")) - 1;
			$this->_filtre = $this->getValeurVerif($this->_request->get("valeur_4"));
		} else if (($this->_request->get("caction") == "box_evenements") && ($this->_request->get("valeur_1") == "s")) {
			$this->_page = $this->getValeurVerif($this->_request->get("valeur_3")) + 1;
			$this->_filtre = $this->getValeurVerif($this->_request->get("valeur_4"));
		} else {
			$this->_page = 1;
			$this->_filtre = -1;
		}

		if ($this->_page < 1) {
			$this->_page = 1;
		}
		$this->_nbMax = $this->view->config->game->evenements->nb_affiche;
	}

	private function getValeurVerif($val) {
		if (((int)$val.""!=$val."")) {
			throw new Zend_Exception(get_class($this)." Valeur invalide : val=".$val);
		} else {
			return (int)$val;
		}
	}
}