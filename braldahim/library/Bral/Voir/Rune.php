<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: $
 * $Author: $
 * $LastChangedDate: $
 * $LastChangedRevision: $
 * $LastChangedBy: $
 */
class Bral_Voir_Rune {

	function __construct($request, $view) {
		Zend_Loader::loadClass("Rune");
		Zend_Loader::loadClass("HistoriqueRune");
		Zend_Loader::loadClass("TypeHistoriqueRune");
		Zend_Loader::loadClass("Bral_Util_Rune");
		Zend_Loader::loadClass("Bral_Helper_DetailRune");

		$this->_request = $request;
		$this->view = $view;
	}

	function getNomInterne() {
		return "box_voir_rune_inner";
	}

	function setDisplay($display) {
		$this->view->display = $display;
	}

	function render() {
		$this->view->rune = null;
		$this->view->connu = false;

		$val = $this->_request->get("rune");
		if ($val != "" && ((int)$val."" == $val."")) {
			return $this->renderData();
		} else {
			$this->view->flux = $this->view->render("voir/rune/profil.phtml");;
			return $this->view->render("voir/rune.phtml");
		}
	}

	private function renderData() {
		$runeTable = new Rune();
		$idRune = Bral_Util_Controle::getValeurIntVerif($this->_request->get("rune"));
		$runeRowset = $runeTable->findByIdRuneWithDetails($idRune);
		if (count($runeRowset) == 1) {
			$this->view->rune = $this->prepareRune($runeRowset[0]);
			$this->view->connu = true;
		}

		if ($this->_request->get("menu") == "historique" && $this->view->connu != null) {
			return $this->renderHistorique();
		} else {
			if ($this->_request->get("direct") == "historique") {
				$flux = $this->renderHistorique();
			} else {
				$flux = $this->view->render("voir/rune/profil.phtml");
			}
			$this->view->flux = $flux;
			return $this->view->render("voir/rune.phtml");
		}
	}

	private function prepareRune($p) {
		$rune = array(
					"id_rune" => $p["id_rune"],
					"id_type_rune" => $p["id_type_rune"],
					"nom_type" => $p["nom_type_rune"],
		);
		return $rune;
	}

	function renderHistorique() {
		$this->preparePage();

		$suivantOk = false;
		$precedentOk = false;
		$tabHistorique = null;
		$tabTypeHistorique = null;
		$historiqueRuneTable = new HistoriqueRune();
		$historiqueRunes = $historiqueRuneTable->findByIdRune($this->view->rune["id_rune"], $this->_page, $this->_nbMax, $this->_filtre);

		foreach ($historiqueRunes as $p) {
			$tabHistorique[] = array(
				"type" => $p["nom_type_historique_rune"],
				"date" => Bral_Util_ConvertDate::get_datetime_mysql_datetime('\l\e d/m/y \&\a\g\r\a\v\e; H:i:s',$p["date_historique_rune"]),
				"details" => $p["details_historique_rune"],
			);
		}

		$typeHistoriqueRuneTable = new TypeHistoriqueRune();
		$typeHistoriqueRune = $typeHistoriqueRuneTable->fetchall(null, array("nom_type_historique_rune"));

		$tabTypeHistorique[] = array(
				"id_type_historique" => -1,
				"nom" => "(Tous)");

		foreach ($typeHistoriqueRune as $t) {
			$tabTypeHistorique[] = array(
					"id_type_historique" => $t->id_type_historique_rune,
					"nom" => $t->nom_type_historique_rune
			);
		}

		if ($this->_page == 1) {
			$precedentOk = false;
		} else {
			$precedentOk = true;
		}

		if (count($tabHistorique) == 0 || count($tabHistorique) < $this->_nbMax) {
			$suivantOk = false;
		} else {
			$suivantOk = true;
		}

		$this->view->precedentOk = $precedentOk;
		$this->view->suivantOk = $suivantOk;
		$this->view->historique = $tabHistorique;
		$this->view->typeHistorique = $tabTypeHistorique;
		$this->view->nbHistorique = count($this->view->historique);

		$this->view->nom_interne = $this->getNomInterne();
		$this->view->page = $this->_page;
		$this->view->filtre = $this->_filtre;
		return $this->view->render("voir/rune/historique.phtml");
	}

	private function preparePage() {
		$this->_page = 1;
		if (($this->_request->get("caction") == "ask_voir_rune") && ($this->_request->get("valeur_1") == "f")) {
			$this->_filtre = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_2"));
		} else if (($this->_request->get("caction") == "ask_voir_rune") && ($this->_request->get("valeur_1") == "p")) { // si le joueur a clique sur une icone
			$this->_page = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_3")) - 1;
			$this->_filtre = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_4"));
		} else if (($this->_request->get("caction") == "ask_voir_rune") && ($this->_request->get("valeur_1") == "s")) {
			$this->_page = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_3")) + 1;
			$this->_filtre = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_4"));
		} else {
			$this->_page = 1;
			$this->_filtre = -1;
		}

		if ($this->_page < 1) {
			$this->_page = 1;
		}
		$this->_nbMax = $this->view->config->game->historique->rune->nb_affiche;
	}
}
