<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Box_Communaute_Membres extends Bral_Box_Box
{

	function getTitreOnglet()
	{
		return "Membres";
	}

	function getNomInterne()
	{
		return "box_communaute_membres";
	}

	function getChargementInBoxes()
	{
		return false;
	}

	function setDisplay($display)
	{
		$this->view->display = $display;
	}

	function getListBoxRefresh()
	{
	}

	function prepareCommun()
	{
	}

	function prepareFormulaire()
	{
	}

	function prepareResultat()
	{
	}

	function render()
	{
		Zend_Loader::loadClass("Communaute");
		Zend_Loader::loadClass("RangCommunaute");
		Zend_Loader::loadClass("TypeLieu");
		Zend_Loader::loadClass("Bral_Util_Communaute");
		Zend_Loader::loadClass("Bral_Helper_Profil");
		Zend_Loader::loadClass("Bral_Helper_Communaute");

		if ($this->view->affichageInterne) {
			$this->preparePage();
			$this->prepareData();
		}
		$this->view->nom_interne = $this->getNomInterne();
		return $this->view->render("interface/communaute/membres.phtml");
	}

	function prepareData()
	{
		$communaute = null;
		$this->view->tri = "";
		$this->view->filtre = "";
		$this->view->page = "";
		$this->view->precedentOk = false;
		$this->view->suivantOk = false;

		$communauteTable = new Communaute();
		$communauteRowset = $communauteTable->findById($this->view->user->id_fk_communaute_braldun);
		if (count($communauteRowset) == 1) {
			$communaute = $communauteRowset[0];
		}

		if ($communaute == null) {
			throw new Zend_Exception(get_class($this) . " Communaute Invalide");
		}

		$braldunTable = new Braldun();
		$nbMembresTotal = $braldunTable->countByIdCommunaute($communaute["id_communaute"]);

		$braldunRowset = $braldunTable->findByIdCommunaute($communaute["id_communaute"], $this->_filtre, $this->_page, $this->_nbMax, $this->_ordreSql, $this->_sensOrdreSql);
		$tabMembres = null;

		$niveauBaraquements = Bral_Util_Communaute::getNiveauDuLieu($this->view->user->id_fk_communaute_braldun, TypeLieu::ID_TYPE_BARAQUEMENT);
		$idList = null;

		foreach ($braldunRowset as $m) {
			$tabMembres[$m["id_braldun"]] = array(
				"id_braldun" => $m["id_braldun"],
				"nom_braldun" => $m["nom_braldun"],
				"prenom_braldun" => $m["prenom_braldun"],
				"niveau_braldun" => $m["niveau_braldun"],
				"sexe_braldun" => $m["sexe_braldun"],

				"x_braldun" => $m["x_braldun"],
				"y_braldun" => $m["y_braldun"],
				"z_braldun" => $m["z_braldun"],

				"pa_braldun" => $m["pa_braldun"],
				"date_fin_tour_braldun" => $m["date_fin_tour_braldun"],

				"pv_restant_braldun" => $m["pv_restant_braldun"],
				"vigueur_base_braldun" => $m["vigueur_base_braldun"],
				"pv_max_bm_braldun" => $m["pv_max_bm_braldun"],

				"duree_prochain_tour_braldun" => $m["duree_prochain_tour_braldun"],
				"duree_courant_tour_braldun" => $m["duree_courant_tour_braldun"],
				"date_debut_tour_braldun" => $m["date_debut_tour_braldun"],
				"date_fin_latence_braldun" => $m["date_fin_latence_braldun"],
				"date_debut_cumul_braldun" => $m["date_debut_cumul_braldun"],
				"date_fin_tour_braldun" => $m["date_fin_tour_braldun"],

				"date_entree" => $m["date_entree_communaute_braldun"],
				"id_rang_communaute" => $m["id_rang_communaute"],
				"nom_rang_communaute" => $m["nom_rang_communaute"],
				"ordre_rang_communaute" => $m["ordre_rang_communaute"],

				"force_base_braldun" => $m["force_base_braldun"],
				"agilite_base_braldun" => $m["agilite_base_braldun"],
				"vigueur_base_braldun" => $m["vigueur_base_braldun"],
				"sagesse_base_braldun" => $m["sagesse_base_braldun"],

				"force_bm_braldun" => $m["force_bm_braldun"],
				"agilite_bm_braldun" => $m["agilite_bm_braldun"],
				"vigueur_bm_braldun" => $m["vigueur_bm_braldun"],
				"sagesse_bm_braldun" => $m["sagesse_bm_braldun"],

				"force_bbdf_braldun" => $m["force_bbdf_braldun"],
				"agilite_bbdf_braldun" => $m["agilite_bbdf_braldun"],
				"vigueur_bbdf_braldun" => $m["vigueur_bbdf_braldun"],
				"sagesse_bbdf_braldun" => $m["sagesse_bbdf_braldun"],

				"vue_bm_braldun" => $m["vue_bm_braldun"],
			);
			$idList[] = $m["id_braldun"];
		}

		Zend_Loader::loadClass('BraldunsMetiers');
		$braldunsMetiersTable = new BraldunsMetiers();
		$braldunsMetierRowset = $braldunsMetiersTable->findMetiersByBraldunIdList($idList);
		$tabMetiersBralduns = array();

		if ($braldunsMetierRowset != null && count($braldunsMetierRowset) > 0) {
			foreach ($braldunsMetierRowset as $m) {
				if ($tabMembres[$m["id_fk_braldun_hmetier"]]["sexe_braldun"] == 'feminin') {
					$nom_metier = $m["nom_feminin_metier"];
				} else {
					$nom_metier = $m["nom_masculin_metier"];
				}

				$t = array("id_metier" => $m["id_metier"],
					"nom" => $nom_metier,
					"nom_systeme" => $m["nom_systeme_metier"],
					"est_actif" => $m["est_actif_hmetier"],
					"date_apprentissage" => Bral_Util_ConvertDate::get_date_mysql_datetime("d/m/Y", $m["date_apprentissage_hmetier"]),
				);

				if ($m["est_actif_hmetier"] == "oui") {
					$tabMetiersBralduns[$m["id_fk_braldun_hmetier"]]["tabMetierCourant"] = $t;
				}

				if ($m["est_actif_hmetier"] == "non") {
					$tabMetiersBralduns[$m["id_fk_braldun_hmetier"]]["tabMetiers"][] = $t;
				}
			}
		}

		$rangCommunauteTable = new RangCommunaute();
		$rangsCommunauteRowset = $rangCommunauteTable->findByIdCommunaute($communaute["id_communaute"]);
		$tabRangs = null;

		foreach ($rangsCommunauteRowset as $r) {
			$tabRangs[] = array(
				"id_type_rang" => $r["id_rang_communaute"],
				"nom" => $r["nom_rang_communaute"],
				"ordre_rang_communaute" => $r["ordre_rang_communaute"],
			);
		}

		if ($this->_page == 1) {
			$this->view->precedentOk = false;
		} else {
			$this->view->precedentOk = true;
		}

		if (count($tabMembres) == 0) {
			$this->view->suivantOk = false;
		} else {
			$this->view->suivantOk = true;
		}

		$this->view->tabMetiersBralduns = $tabMetiersBralduns;
		$this->view->niveauBaraquements = $niveauBaraquements;
		$this->view->page = $this->_page;
		$this->view->filtre = $this->_filtre;
		$this->view->ordre = $this->_ordre;
		$this->view->sensOrdre = $this->_sensOrdre;
		$this->view->tabRangs = $tabRangs;
		$this->view->tabMembres = $tabMembres;
		$this->view->nbMembresTotal = $nbMembresTotal;
		$this->view->nom_interne = $this->getNomInterne();
	}

	private function preparePage()
	{
		$this->_page = 1;

		if (($this->_request->get("box") == "box_communaute_membres") && ($this->_request->get("valeur_1") == "f")) {
			$this->_filtre = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_2"));
			$ordre = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_5"));
			$sensOrdre = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_6"));
		} else if (($this->_request->get("box") == "box_communaute_membres") && ($this->_request->get("valeur_1") == "p")) {
			$this->_page = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_3")) - 1;
			$this->_filtre = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_4"));
			$ordre = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_5"));
			$sensOrdre = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_6"));
		} else if (($this->_request->get("box") == "box_communaute_membres") && ($this->_request->get("valeur_1") == "s")) {
			$this->_page = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_3")) + 1;
			$this->_filtre = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_4"));
			$ordre = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_5"));
			$sensOrdre = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_6"));
		} else if (($this->_request->get("box") == "box_communaute_membres") && ($this->_request->get("valeur_1") == "o")) {
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

	private function getChampOrdre($ordre)
	{
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
			$retour = "date_entree_communaute_braldun";
		} elseif ($ordre == 6) {
			$retour = "id_rang_communaute";
		} elseif ($ordre == 7) {
			$retour = "force_base_braldun";
		} elseif ($ordre == 8) {
			$retour = "agilite_base_braldun";
		} elseif ($ordre == 9) {
			$retour = "vigueur_base_braldun";
		} elseif ($ordre == 10) {
			$retour = "sagesse_base_braldun";
		} elseif ($ordre == 11) {
			$retour = "vue_bm_braldun";
		} elseif ($ordre == 12) {
			$retour = "pa_braldun";
		} elseif ($ordre == 13) {
			$retour = "date_fin_tour_braldun";
		} elseif ($ordre == 14) {
			$retour = "pv_restant_braldun";
		} else {
			$retour = "prenom_braldun";
		}
		return $retour;
	}

	private function getSensOrdre($sensOrdre)
	{
		$sens = " ASC ";
		if ($sensOrdre % 2 == 0) {
			return " DESC ";
		} else {
			return " ASC ";
		}
		return $sens;
	}
}
