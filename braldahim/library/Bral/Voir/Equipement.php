<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Voir_Equipement
{

	function __construct($request, $view)
	{
		Zend_Loader::loadClass("Equipement");
		Zend_Loader::loadClass("Bral_Helper_ProfilEquipement");
		Zend_Loader::loadClass("HistoriqueEquipement");
		Zend_Loader::loadClass("TypeHistoriqueEquipement");
		Zend_Loader::loadClass("Bral_Util_Equipement");
		Zend_Loader::loadClass("Bral_Helper_DetailEquipement");

		$this->_request = $request;
		$this->view = $view;
	}

	function getNomInterne()
	{
		return "box_voir_equipement_inner";
	}

	function setDisplay($display)
	{
		$this->view->display = $display;
	}

	function render()
	{
		$this->view->equipement = null;
		$this->view->connu = false;

		$val = $this->_request->get("equipement");
		if ($val != "" && ((int)$val . "" == $val . "")) {
			return $this->renderData();
		} else {
			$this->view->flux = $this->view->render("voir/equipement/profil.phtml");
			;
			return $this->view->render("voir/equipement.phtml");
		}
	}

	private function renderData()
	{
		$equipementTable = new Equipement();
		$idEquipement = Bral_Util_Controle::getValeurIntVerif($this->_request->get("equipement"));
		$equipementRowset = $equipementTable->findByIdEquipementWithDetails($idEquipement);
		if (count($equipementRowset) == 1) {
			$this->view->equipement = $this->prepareEquipement($equipementRowset[0]);
			$this->view->connu = true;
		}

		if ($this->_request->get("menu") == "historique" && $this->view->connu != null) {
			return $this->renderHistorique();
		} else {
			if ($this->_request->get("direct") == "historique") {
				$flux = $this->renderHistorique();
			} else {
				$flux = $this->view->render("voir/equipement/profil.phtml");
			}
			$this->view->flux = $flux;
			return $this->view->render("voir/equipement.phtml");
		}
	}

	private function prepareEquipement($e)
	{
		$tabWhere = null;
		Zend_Loader::loadClass("EquipementRune");
		$equipementRuneTable = new EquipementRune();
		Zend_Loader::loadClass("EquipementBonus");
		$equipementBonusTable = new EquipementBonus();
		$equipements = null;

		$idEquipements = null;
		$idEquipements[] = $e["id_equipement"];

		$equipementRunes = $equipementRuneTable->findByIdsEquipement($idEquipements);
		unset($equipementRuneTable);
		$equipementBonus = $equipementBonusTable->findByIdsEquipement($idEquipements);
		unset($equipementBonusTable);

		$runes = null;
		if (count($equipementRunes) > 0) {
			foreach ($equipementRunes as $r) {
				if ($r["id_equipement_rune"] == $e["id_equipement"]) {
					$runes[] = array(
						"id_rune_equipement_rune" => $r["id_rune_equipement_rune"],
						"id_fk_type_rune" => $r["id_fk_type_rune"],
						"nom_type_rune" => $r["nom_type_rune"],
						"image_type_rune" => $r["image_type_rune"],
						"effet_type_rune" => $r["effet_type_rune"],
					);
				}
			}
		}

		$bonus = null;
		if (count($equipementBonus) > 0) {
			foreach ($equipementBonus as $b) {
				if ($b["id_equipement_bonus"] == $e["id_equipement"]) {
					$bonus = $b;
					break;
				}
			}
		}

		$equipement = array(
			"id_equipement" => $e["id_equipement"],
			"nom" => Bral_Util_Equipement::getNomByIdRegion($e, $e["id_fk_region_equipement"]),
			"nom_standard" => $e["nom_type_equipement"],
			"qualite" => $e["nom_type_qualite"],
			"emplacement" => $e["nom_type_emplacement"],
			"niveau" => $e["niveau_recette_equipement"],
			"id_type_equipement" => $e["id_type_equipement"],
			"id_type_emplacement" => $e["id_type_emplacement"],
			"nom_systeme_type_emplacement" => $e["nom_systeme_type_emplacement"],
			"nb_runes" => $e["nb_runes_equipement"],
			"id_fk_recette_equipement" => $e["id_fk_recette_equipement"],
			"armure" => $e["armure_equipement"],
			"force" => $e["force_equipement"],
			"agilite" => $e["agilite_equipement"],
			"vigueur" => $e["vigueur_equipement"],
			"sagesse" => $e["sagesse_equipement"],
			"vue" => $e["vue_recette_equipement"],
			"attaque" => $e["attaque_equipement"],
			"degat" => $e["degat_equipement"],
			"defense" => $e["defense_equipement"],
			"suffixe" => $e["suffixe_mot_runique"],
			"poids" => $e["poids_equipement"],
			"etat_courant" => $e["etat_courant_equipement"],
			"etat_initial" => $e["etat_initial_equipement"],
			"ingredient" => $e["nom_type_ingredient"],
			"runes" => $runes,
			"bonus" => $bonus,
		);
		return $equipement;
	}

	function renderHistorique()
	{
		Zend_Loader::loadClass("Bral_Util_Equipement");

		if ($this->view->user != null && $this->view->user->id_braldun != null) {
			$this->view->possede = Bral_Util_Equipement::possedeEquipement($this->view->user->id_braldun, $this->view->equipement["id_equipement"]);
		} else {
			$this->view->possede = false;
		}

		$this->preparePage();

		$suivantOk = false;
		$precedentOk = false;
		$tabHistorique = null;
		$tabTypeHistorique = null;
		$historiqueEquipementTable = new HistoriqueEquipement();
		$historiqueEquipements = $historiqueEquipementTable->findByIdEquipement($this->view->equipement["id_equipement"], $this->_page, $this->_nbMax, $this->_filtre);

		foreach ($historiqueEquipements as $p) {
			$tabHistorique[] = array(
				"type" => $p["nom_type_historique_equipement"],
				"date" => Bral_Util_ConvertDate::get_datetime_mysql_datetime('\l\e d/m/y \&\a\g\r\a\v\e; H:i:s', $p["date_historique_equipement"]),
				"details" => $p["details_historique_equipement"],
			);
		}

		$typeHistoriqueEquipementTable = new TypeHistoriqueEquipement();
		$typeHistoriqueEquipement = $typeHistoriqueEquipementTable->fetchall(null, array("nom_type_historique_equipement"));

		$tabTypeHistorique[] = array(
			"id_type_historique" => -1,
			"nom" => "(Tous)");

		foreach ($typeHistoriqueEquipement as $t) {
			$tabTypeHistorique[] = array(
				"id_type_historique" => $t->id_type_historique_equipement,
				"nom" => $t->nom_type_historique_equipement
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
		return $this->view->render("voir/equipement/historique.phtml");
	}

	private function preparePage()
	{
		$this->_page = 1;
		if (($this->_request->get("caction") == "ask_voir_equipement") && ($this->_request->get("valeur_1") == "f")) {
			$this->_filtre = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_2"));
		} else if (($this->_request->get("caction") == "ask_voir_equipement") && ($this->_request->get("valeur_1") == "p")) { // si le joueur a clique sur une icone
			$this->_page = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_3")) - 1;
			$this->_filtre = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_4"));
		} else if (($this->_request->get("caction") == "ask_voir_equipement") && ($this->_request->get("valeur_1") == "s")) {
			$this->_page = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_3")) + 1;
			$this->_filtre = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_4"));
		} else {
			$this->_page = 1;
			$this->_filtre = -1;
		}

		if ($this->_page < 1) {
			$this->_page = 1;
		}
		$this->_nbMax = $this->view->config->game->historique->equipement->nb_affiche;
	}
}
