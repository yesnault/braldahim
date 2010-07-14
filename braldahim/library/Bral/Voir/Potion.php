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
class Bral_Voir_Potion {

	function __construct($request, $view) {
		Zend_Loader::loadClass("Potion");
		Zend_Loader::loadClass("HistoriquePotion");
		Zend_Loader::loadClass("TypeHistoriquePotion");
		Zend_Loader::loadClass("Bral_Util_Potion");
		Zend_Loader::loadClass("Bral_Helper_DetailPotion");

		$this->_request = $request;
		$this->view = $view;
	}

	function getNomInterne() {
		return "box_voir_potion_inner";
	}

	function setDisplay($display) {
		$this->view->display = $display;
	}

	function render() {
		$this->view->potion = null;
		$this->view->connu = false;

		$val = $this->_request->get("potion");
		if ($val != "" && ((int)$val."" == $val."")) {
			return $this->renderData();
		} else {
			$this->view->flux = $this->view->render("voir/potion/profil.phtml");;
			return $this->view->render("voir/potion.phtml");
		}
	}

	private function renderData() {
		$potionTable = new Potion();
		$idPotion = Bral_Util_Controle::getValeurIntVerif($this->_request->get("potion"));
		$potionRowset = $potionTable->findByIdPotionWithDetails($idPotion);
		if (count($potionRowset) == 1) {
			$this->view->potion = $this->preparePotion($potionRowset[0]);
			$this->view->connu = true;
		}

		if ($this->_request->get("menu") == "historique" && $this->view->connu != null) {
			return $this->renderHistorique();
		} else {
			if ($this->_request->get("direct") == "historique") {
				$flux = $this->renderHistorique();
			} else {
				$flux = $this->view->render("voir/potion/profil.phtml");
			}
			$this->view->flux = $flux;
			return $this->view->render("voir/potion.phtml");
		}
	}

	private function preparePotion($p) {
		$potion = array(
					"id_potion" => $p["id_potion"],
					"id_type_potion" => $p["id_type_potion"],
					"nom" => $p["nom_type_potion"],
					"qualite" => $p["nom_type_qualite"],
					"niveau" => $p["niveau_potion"],
					"caracteristique" => $p["caract_type_potion"],
					"bm_type" => $p["bm_type_potion"],
					"caracteristique2" => $p["caract2_type_potion"],
					"bm2_type" => $p["bm2_type_potion"],
					"nom_type" => Bral_Util_Potion::getNomType($p["type_potion"]),
		);
		return $potion;
	}

	function renderHistorique() {
		Zend_Loader::loadClass("Bral_Util_Potion");

		if ($this->view->user != null && $this->view->user->id_braldun != null) {
			$this->view->possede = Bral_Util_Potion::possedePotion($this->view->user->id_braldun, $this->view->potion["id_potion"]);
		} else {
			$this->view->possede = false;
		}

		$this->preparePage();

		$suivantOk = false;
		$precedentOk = false;
		$tabHistorique = null;
		$tabTypeHistorique = null;
		$historiquePotionTable = new HistoriquePotion();
		$historiquePotions = $historiquePotionTable->findByIdPotion($this->view->potion["id_potion"], $this->_page, $this->_nbMax, $this->_filtre);

		foreach ($historiquePotions as $p) {
			$tabHistorique[] = array(
				"type" => $p["nom_type_historique_potion"],
				"date" => Bral_Util_ConvertDate::get_datetime_mysql_datetime('\l\e d/m/y \&\a\g\r\a\v\e; H:i:s',$p["date_historique_potion"]),
				"details" => $p["details_historique_potion"],
			);
		}

		$typeHistoriquePotionTable = new TypeHistoriquePotion();
		$typeHistoriquePotion = $typeHistoriquePotionTable->fetchall(null, array("nom_type_historique_potion"));

		$tabTypeHistorique[] = array(
				"id_type_historique" => -1,
				"nom" => "(Tous)");

		foreach ($typeHistoriquePotion as $t) {
			$tabTypeHistorique[] = array(
					"id_type_historique" => $t->id_type_historique_potion,
					"nom" => $t->nom_type_historique_potion
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
		return $this->view->render("voir/potion/historique.phtml");
	}

	private function preparePage() {
		$this->_page = 1;
		if (($this->_request->get("caction") == "ask_voir_potion") && ($this->_request->get("valeur_1") == "f")) {
			$this->_filtre = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_2"));
		} else if (($this->_request->get("caction") == "ask_voir_potion") && ($this->_request->get("valeur_1") == "p")) { // si le joueur a clique sur une icone
			$this->_page = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_3")) - 1;
			$this->_filtre = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_4"));
		} else if (($this->_request->get("caction") == "ask_voir_potion") && ($this->_request->get("valeur_1") == "s")) {
			$this->_page = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_3")) + 1;
			$this->_filtre = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_4"));
		} else {
			$this->_page = 1;
			$this->_filtre = -1;
		}

		if ($this->_page < 1) {
			$this->_page = 1;
		}
		$this->_nbMax = $this->view->config->game->historique->potion->nb_affiche;
	}
}
