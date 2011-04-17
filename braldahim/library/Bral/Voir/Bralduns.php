<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Voir_Bralduns {

	function __construct($request, $view) {
		$this->_request = $request;
		$this->view = $view;
		$this->preparePage();
	}

	function getNomInterne() {
		return "box_voir_bralduns_inner";
	}

	function setDisplay($display) {
		$this->view->display = $display;
	}

	private function prepareRender() {
		$communaute = null;
		$this->view->tri = "";
		$this->view->filtre = "";
		$this->view->filtrePrenom = "";
		$this->view->page = "";
		$this->view->precedentOk = false;
		$this->view->suivantOk = false;
		
		$braldunTable = new Braldun();
		$braldunRowset = $braldunTable->findByCriteres($this->_filtre, $this->_page, $this->_nbMax, $this->_ordreSql, $this->_sensOrdreSql, $this->_whereSql);
		$tabBralduns = null;
		$tabNiveaux = $braldunTable->findDistinctNiveau();
		
		
		foreach($braldunRowset as $m) {
			$tabBralduns[] = array(
				"id_braldun" => $m["id_braldun"],
				"nom_braldun" => $m["nom_braldun"],
				"prenom_braldun" => $m["prenom_braldun"],
				"niveau_braldun" => $m["niveau_braldun"],
				"date_creation" => $m["date_creation_braldun"],
				"points_distinctions" => $m["points_distinctions_braldun"],
			);
		}
		
		if ($this->_page == 1) {
			$this->view->precedentOk = false;
		} else {
			$this->view->precedentOk = true;
		}

		if (count($tabBralduns) == 0) {
			$this->view->suivantOk = false;
		} else {
			$this->view->suivantOk = true;
		}
		
		$this->view->page = $this->_page;
		$this->view->filtre = $this->_filtre;
		$this->view->filtrePrenom = $this->_filtrePrenom;
		$this->view->ordre = $this->_ordre;
		$this->view->sensOrdre = $this->_sensOrdre;
		$this->view->tabNiveaux = $tabNiveaux;
		$this->view->tabBralduns = $tabBralduns;
		$this->view->nom_interne = $this->getNomInterne();
	}
	
	public function render() {
		$this->prepareRender();
		if ($this->_request->get("valeur_1") != "") {
			return $this->view->render("/voir/bralduns/liste.phtml");
		} else {
			return $this->view->render("/voir/bralduns.phtml");
		}
	}
	
	private function preparePage() {
		$this->view->tabLettres = Bral_Util_String::getTabLettres();
		
		$this->_page = 1;
		
		if ($this->_request->get("valeur_1") == "f") {
			$this->_filtre = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_2"));
			$this->_filtrePrenom = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_7"));
			$ordre = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_5"));
			$sensOrdre = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_6"));
		} else if ($this->_request->get("valeur_1") == "p") {
			$this->_page = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_3")) - 1;
			$this->_filtre = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_4"));
			$this->_filtrePrenom = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_7"));
			$ordre = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_5"));
			$sensOrdre = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_6"));
		} else if ($this->_request->get("valeur_1") == "s") {
			$this->_page = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_3")) + 1;
			$this->_filtre = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_4"));
			$this->_filtrePrenom = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_7"));
			$ordre = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_5"));
			$sensOrdre = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_6"));
		} else if ($this->_request->get("valeur_1") == "o") {
			$this->_filtre = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_2"));
			$this->_filtrePrenom = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_7"));
			$ordre = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_5"));
			$sensOrdre = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_6")) + 1;
		} else {
			$this->_page = 1;
			$this->_filtre = -1;
			$this->_filtrePrenom = -1;
			$ordre = -1;
			$sensOrdre = 0;
		}
		
		$this->_ordre = $ordre;
		$this->_sensOrdre = $sensOrdre;
		$this->_whereSql = $this->getWhereSql($this->_filtrePrenom);
		
		$this->_ordreSql = $this->getChampOrdre($ordre);
		$this->_sensOrdreSql = $this->getSensOrdre($sensOrdre);
		
		if ($this->_page < 1) {
			$this->_page = 1;
		}
		$this->_nbMax = $this->view->config->communaute->membres->nb_affiche;
	}
	
	private function getChampOrdre($ordre) {
		$retour = "";
		if ($ordre == 1) {
			$retour = "prenom_braldun";
		} elseif ($ordre == 2) {
			$retour = "nom_braldun";
		} elseif ($ordre == 3) {
			$retour = "id_braldun";
		} elseif ($ordre == 4) {
			$retour = "niveau_braldun";
		} elseif ($ordre == 5) {
			$retour = "date_creation_braldun";
		} elseif ($ordre == 6) {
			$retour = "points_distinctions_braldun";
		} else {
			$retour = "points_distinctions_braldun";
		}
		return $retour;
	}
	
	private function getSensOrdre($sensOrdre) {
		$sens = " ASC ";
		if ($sensOrdre % 2 == 0) {
			return " DESC ";
		} else {
			return " ASC ";
		}
		return $sens;
	}
	
	private function getWhereSql($numLettre) {
		$where = null;
		
		if ($numLettre >= 0 && $numLettre <= 25) {
			$where = " lcase(prenom_braldun) like '".mb_strtolower($this->view->tabLettres[$numLettre])."%'";
		}
		return $where;
	}
}
