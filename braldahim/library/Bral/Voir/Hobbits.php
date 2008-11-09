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
class Bral_Voir_Hobbits {

	function __construct($request, $view) {
		$this->_request = $request;
		$this->view = $view;
		$this->preparePage();
	}

	function getNomInterne() {
		return "box_voir_hobbits_inner";
	}

	function setDisplay($display) {
		$this->view->display = $display;
	}

	private function prepareRender() {
		$communaute = null;
		$this->view->tri = "";
		$this->view->filtre = "";
		$this->view->page = "";
		$this->view->precedentOk = false;
		$this->view->suivantOk = false;
		
		$hobbitTable = new Hobbit();
		
		$hobbitRowset = $hobbitTable->findByCriteres($this->_filtre, $this->_page, $this->_nbMax, $this->_ordreSql, $this->_sensOrdreSql);
		$tabHobbits = null;
		$tabNiveaux = null;
		
		foreach($hobbitRowset as $m) {
			$tabHobbits[] = array(
				"id_hobbit" => $m["id_hobbit"],
				"nom_hobbit" => $m["nom_hobbit"],
				"prenom_hobbit" => $m["prenom_hobbit"],
				"niveau_hobbit" => $m["niveau_hobbit"],
				"date_creation" => $m["date_creation_hobbit"],
			);
			$tabNiveaux[$m["niveau_hobbit"]] = $m["niveau_hobbit"];
		}
		
		
		if ($this->_page == 1) {
			$this->view->precedentOk = false;
		} else {
			$this->view->precedentOk = true;
		}

		if (count($tabHobbits) == 0) {
			$this->view->suivantOk = false;
		} else {
			$this->view->suivantOk = true;
		}
		
		$this->view->page = $this->_page;
		$this->view->filtre = $this->_filtre;
		$this->view->ordre = $this->_ordre;
		$this->view->sensOrdre = $this->_sensOrdre;
		$this->view->tabNiveaux = $tabNiveaux;
		$this->view->tabHobbits = $tabHobbits;
		$this->view->nom_interne = $this->getNomInterne();
	}
	
	public function render() {
		$this->prepareRender();
		if ($this->_request->get("valeur_1") != "") {
			return $this->view->render("/voir/hobbits/liste.phtml");
		} else {
			return $this->view->render("/voir/hobbits.phtml");
		}
	}
	
	private function preparePage() {
		$this->_page = 1;
		
		if ($this->_request->get("valeur_1") == "f") {
			$this->_filtre = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_2"));
			$ordre = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_5"));
			$sensOrdre = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_6"));
		} else if ($this->_request->get("valeur_1") == "p") {
			$this->_page = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_3")) - 1;
			$this->_filtre = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_4"));
			$ordre = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_5"));
			$sensOrdre = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_6"));
		} else if ($this->_request->get("valeur_1") == "s") {
			$this->_page = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_3")) + 1;
			$this->_filtre = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_4"));
			$ordre = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_5"));
			$sensOrdre = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_6"));
		} else if ($this->_request->get("valeur_1") == "o") {
			$this->_filtre = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_2"));
			$ordre = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_5"));
			$sensOrdre = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_6")) + 1;
		} else {
			$this->_page = 1;
			$this->_filtre = -1;
			$ordre = -1;
			$sensOrdre = 1;
		}
		
		$this->_ordre = $ordre;
		$this->_sensOrdre = $sensOrdre;
		
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
			$retour = "prenom_hobbit";
		} elseif ($ordre == 2) {
			$retour = "nom_hobbit";
		} elseif ($ordre == 3) {
			$retour = "id_hobbit";
		} elseif ($ordre == 4) {
			$retour = "niveau_hobbit";
		} elseif ($ordre == 5) {
			$retour = "date_creation_hobbit";
		} else {
			$retour = "prenom_hobbit";
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
}
