<?php

class Bral_Voir_Hobbit {

	function __construct($request, $view) {
		Zend_Loader::loadClass("Evenement");
		Zend_Loader::loadClass("TypeEvenement");
		Zend_Loader::loadClass("Communaute");

		$this->_request = $request;
		$this->view = $view;
	}

	function getNomInterne() {
		return "box_voir_hobbit_inner";
	}

	function setDisplay($display) {
		$this->view->display = $display;
	}

	function render() {
		$this->view->connu = false;
		$this->view->hobbit = null;
		$this->view->communaute = null;
		
		$hobbitTable = new Hobbit();
		$hobbitRowset = $hobbitTable->findById($this->getValeurVerif($this->_request->get("hobbit")));
		if (count($hobbitRowset) == 1) {
			$hobbitRowset = $hobbitRowset->toArray();
			$this->view->hobbit = $hobbitRowset;
			$this->view->connu = true;
			
			if ($this->view->hobbit["id_fk_communaute_hobbit"] != null) {
				$communauteTable = new Communaute();
				$communaute = $communauteTable->findById($this->view->hobbit["id_fk_communaute_hobbit"]);
				if (count($communaute) == 1) {
					$this->view->communaute = $communaute[0];
				}
			}
			
		} else {
			$hobbit = null;
		}
		
		if ($this->_request->get("menu") == "evenements" && $this->view->connu != null) {
			return $this->renderEvenements();
		} else { 
			return $this->view->render("voir/hobbit.phtml");
		}
	}
	
	function renderEvenements() {
		$this->preparePage();
		
		$suivantOk = false;
		$precedentOk = false;
		$tabEvenements = null;
		$tabTypeEvenements = null;
		$evenementTable = new Evenement();
		$evenements = $evenementTable->findByIdHobbit($this->view->hobbit["id_hobbit"], $this->_page, $this->_nbMax, $this->_filtre);

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
		return $this->view->render("voir/hobbit/evenements.phtml");
	}
	
	private function preparePage() {
		$this->_page = 1;
		if (($this->_request->get("caction") == "ask_voir_hobbit") && ($this->_request->get("valeur_1") == "f")) {
			$this->_filtre = $this->getValeurVerif($this->_request->get("valeur_2"));
		} else if (($this->_request->get("caction") == "ask_voir_hobbit") && ($this->_request->get("valeur_1") == "p")) { // si le joueur a clique sur une icone
			$this->_page = $this->getValeurVerif($this->_request->get("valeur_3")) - 1;
			$this->_filtre = $this->getValeurVerif($this->_request->get("valeur_4"));
		} else if (($this->_request->get("caction") == "ask_voir_hobbit") && ($this->_request->get("valeur_1") == "s")) {
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
