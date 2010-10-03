<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Voir_Braldun {

	function __construct($request, $view) {
		Zend_Loader::loadClass("Evenement");
		Zend_Loader::loadClass("TypeEvenement");
		Zend_Loader::loadClass("BraldunsDistinction");
		Zend_Loader::loadClass("BraldunsTitres");
		Zend_Loader::loadClass("Communaute");
		Zend_Loader::loadClass("Bral_Util_Metier");
		Zend_Loader::loadClass("Bral_Util_Titre");
		Zend_Loader::loadClass("Bral_Util_Distinction");
		Zend_Loader::loadClass("Bral_Helper_ProfilEquipement");
		Zend_Loader::loadClass('AncienBraldun');
		Zend_Loader::loadClass("Charrette");

		$this->_request = $request;
		$this->view = $view;
	}

	function getNomInterne() {
		return "box_voir_braldun_inner";
	}

	function setDisplay($display) {
		$this->view->display = $display;
	}

	function render() {
		$this->view->ancien = false;
		$this->view->connu = false;
		$this->view->braldun = null;
		$this->view->communaute = null;

		$val = $this->_request->get("braldun");
		if ($val != "" && ((int)$val."" == $val."")) {
			return $this->renderData();
		} else {
			$this->view->flux = $this->view->render("voir/braldun/profil.phtml");;
			return $this->view->render("voir/braldun.phtml");
		}
	}

	private function renderData() {
		$tabMetier["tabMetierCourant"] = null;
		$tabMetier["tabMetiers"] = null;
		$tabMetier["possedeMetier"] = false;
		$tabTitre["tabTitres"] = null;
		$tabDistinction["tabDistinctions"] = null;

		$braldunTable = new Braldun();
		$idBraldun = Bral_Util_Controle::getValeurIntVerif($this->_request->get("braldun"));
		$braldunRowset = $braldunTable->findById($idBraldun);
		if (count($braldunRowset) == 1) {
			$braldunRowset = $braldunRowset->toArray();
			$this->view->braldun = $braldunRowset;
			$this->view->connu = true;

			if ($this->view->braldun["id_fk_communaute_braldun"] != null) {
				$communauteTable = new Communaute();
				$communaute = $communauteTable->findById($this->view->braldun["id_fk_communaute_braldun"]);
				if (count($communaute) == 1) {
					$this->view->communaute = $communaute[0];
				}
			}
			$tabMetier = Bral_Util_Metier::prepareMetier($this->view->braldun["id_braldun"], $this->view->braldun["sexe_braldun"]);
			$tabTitre = Bral_Util_Titre::prepareTitre($this->view->braldun["id_braldun"], $this->view->braldun["sexe_braldun"]);
		} else {
			$this->rechercheAncien($idBraldun);
		}

		if ($this->view->ancien == false) {
			Zend_Loader::loadClass("Bral_Util_Equipement");
			$tabEmplacementsEquipement = Bral_Util_Equipement::getTabEmplacementsEquipement($this->view->braldun["id_braldun"], $this->view->braldun["niveau_braldun"]);
			$this->view->tabTypesEmplacement = $tabEmplacementsEquipement["tabTypesEmplacement"];

			$this->view->tabMetierCourant = $tabMetier["tabMetierCourant"];
			$this->view->tabMetiers = $tabMetier["tabMetiers"];
			$this->view->possedeMetier = $tabMetier["possedeMetier"];
			$this->view->tabTitres = $tabTitre["tabTitres"];

			$charretteTable = new Charrette();
			$nbCharrette = $charretteTable->countByIdBraldun($this->view->braldun["id_braldun"]);
			if ($nbCharrette > 0) {
				$this->view->possedeCharrette = "oui";
			} else {
				$this->view->possedeCharrette = "non";
			}
		}

		if ($this->_request->get("menu") == "evenements" && $this->view->connu != null && $this->view->ancien == false) {
			return $this->renderEvenements();
		} else if ($this->_request->get("menu") == "distinctions" && $this->view->connu != null && $this->view->ancien == false) {
			return $this->renderDistinctions();
		} else if ($this->_request->get("menu") == "famille" && $this->view->connu != null) {
			return $this->renderFamille();
		} else {
			if ($this->_request->get("direct") == "evenements") {
				$flux = $this->renderEvenements();
			} else if ($this->_request->get("direct") == "distinctions") {
				$flux = $this->renderDistinctions();
			} else if ($this->_request->get("direct") == "famille") {
				$flux = $this->renderFamille();
			} else {
				$flux = $this->view->render("voir/braldun/profil.phtml");
			}
			$this->view->flux = $flux;
			return $this->view->render("voir/braldun.phtml");
		}
	}

	private function rechercheAncien($idBraldun) {
		$ancienBraldunTable = new AncienBraldun();
		$braldun = $ancienBraldunTable->findById($idBraldun);
		if ($braldun != null) {
			$this->view->connu = true;
			$this->view->ancien = true;
			$this->view->braldun["id_braldun"] = $braldun["id_braldun_ancien_braldun"];

			$this->view->braldun["nom_braldun"] = $braldun["nom_ancien_braldun"];
			$this->view->braldun["prenom_braldun"] = $braldun["prenom_ancien_braldun"];
			$this->view->braldun["id_fk_nom_initial_braldun"] = $braldun["id_fk_nom_initial_ancien_braldun"];
			$this->view->braldun["email_braldun"] = $braldun["email_ancien_braldun"];
			$this->view->braldun["sexe_braldun"] = $braldun["sexe_ancien_braldun"];
			$this->view->braldun["niveau_braldun"] = $braldun["niveau_ancien_braldun"];
			$this->view->braldun["nb_ko_braldun"] = $braldun["nb_ko_ancien_braldun"];
			$this->view->braldun["nb_braldun_ko_braldun"] = $braldun["nb_braldun_ko_ancien_braldun"];
			$this->view->braldun["nb_plaque_braldun"] = $braldun["nb_plaque_ancien_braldun"];
			$this->view->braldun["nb_braldun_plaquage_braldun"] = $braldun["nb_braldun_plaquage_ancien_braldun"];
			$this->view->braldun["nb_monstre_kill_braldun"] = $braldun["nb_monstre_kill_ancien_braldun"];
			$this->view->braldun["id_fk_mere_braldun"] = $braldun["id_fk_mere_ancien_braldun"];
			$this->view->braldun["id_fk_pere_braldun"] = $braldun["id_fk_pere_ancien_braldun"];
			$this->view->braldun["metiers_ancien_braldun"] = $braldun["metiers_ancien_braldun"];
			$this->view->braldun["titres_ancien_braldun"] = $braldun["titres_ancien_braldun"];
			$this->view->braldun["distinctions_ancien_braldun"] = $braldun["distinctions_ancien_braldun"];
			$this->view->braldun["date_creation_braldun"] = $braldun["date_creation_ancien_braldun"];
		}
	}

	private function prepareMetier() {
		Zend_Loader::loadClass("BraldunsMetiers");
		$braldunsMetiersTable = new BraldunsMetiers();
		$braldunsMetierRowset = $braldunsMetiersTable->findMetiersByBraldunId($this->view->user->id_braldun);
		unset($braldunsMetiersTable);
		$tabMetiers = null;
		$tabMetierCourant = null;
		$possedeMetier = false;

		foreach($braldunsMetierRowset as $m) {
			$possedeMetier = true;

			if ($this->view->user->sexe_braldun == 'feminin') {
				$nom_metier = $m["nom_feminin_metier"];
			} else {
				$nom_metier = $m["nom_masculin_metier"];
			}

			$t = array("id_metier" => $m["id_metier"],
				"nom" => $nom_metier,
				"nom_systeme" => $m["nom_systeme_metier"],
				"est_actif" => $m["est_actif_hmetier"],
				"date_apprentissage" => Bral_Util_ConvertDate::get_date_mysql_datetime("d/m/Y", $m["date_apprentissage_hmetier"]),
				"description" => $m["description_metier"],
			);

			if ($m["est_actif_hmetier"] == "non") {
				$tabMetiers[] = $t;
			}

			if ($m["est_actif_hmetier"] == "oui") {
				$tabMetierCourant = $t;
			}
		}
		unset($braldunsMetierRowset);

		$this->view->tabMetierCourant = $tabMetierCourant;
		$this->view->tabMetiers = $tabMetiers;
		$this->view->possedeMetier = $possedeMetier;
		$this->view->nom_interne = $this->getNomInterne();
	}


	function renderFamille() {

		Zend_Loader::loadClass('Couple');
		$braldunTable = new Braldun();
		$ancienBraldunTable = new AncienBraldun();

		$this->view->pereMereOk = false;
		$pere = null;
		$mere = null;

		$this->view->mereAncienne = false;
		$this->view->pereAncien = false;

		if ($this->view->braldun["id_fk_mere_braldun"] != null && $this->view->braldun["id_fk_pere_braldun"] != null &&
		$this->view->braldun["id_fk_mere_braldun"] != 0 && $this->view->braldun["id_fk_pere_braldun"] != 0 ) {

			$pere = $braldunTable->findById($this->view->braldun["id_fk_pere_braldun"]);
			$mere = $braldunTable->findById($this->view->braldun["id_fk_mere_braldun"]);

			if ($pere == null) {
				$this->view->pereAncien = true;
				$pere = $ancienBraldunTable->findById($this->view->braldun["id_fk_pere_braldun"]);
			}

			if ($mere == null) {
				$this->view->mereAncienne = true;
				$mere = $ancienBraldunTable->findById($this->view->braldun["id_fk_mere_braldun"]);
			}

			$this->view->pereMereOk = true;
		}

		$this->view->pere = $pere;
		$this->view->mere = $mere;

		// on regarde s'il y a des enfants
		$enfants = null;
		$enfantsRowset = $braldunTable->findEnfants($this->view->braldun["sexe_braldun"], $this->view->braldun["id_braldun"]);
		unset($braldunTable);
		$this->view->nbEnfants = count($enfantsRowset);

		if (count($this->view->nbEnfants) > 0) {
			foreach($enfantsRowset as $e) {
				$enfants[] = array("prenom" => $e["prenom_braldun"],
									"nom" => $e["nom_braldun"],
									"id_braldun" => $e["id_braldun"],
									"sexe_braldun" => $e["sexe_braldun"],
									"date_naissance" => $e["date_creation_braldun"]);
			}
			unset($enfantsRowset);
		}
		$this->view->enfants = $enfants;

		// on va chercher les informations du conjoint
		Zend_Loader::loadClass("Bral_Util_Conjoints");
		$this->view->conjoint = Bral_Util_Conjoints::getConjoint($this->view->braldun["sexe_braldun"], $this->view->braldun["id_braldun"]);

		$this->view->dateNaissance = Bral_Util_ConvertDate::get_datetime_mysql_datetime('d/m/y \&\a\g\r\a\v\e; H:i:s',$this->view->braldun["date_creation_braldun"]);
		return $this->view->render("voir/braldun/famille.phtml");
	}

	function renderEvenements() {
		$this->preparePage();

		$suivantOk = false;
		$precedentOk = false;
		$tabEvenements = null;
		$tabTypeEvenements = null;
		$evenementTable = new Evenement();
		$evenements = $evenementTable->findByIdBraldun($this->view->braldun["id_braldun"], $this->_page, $this->_nbMax, $this->_filtre);

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

			if (count($tabEvenements) == 0 || count($tabEvenements) < $this->_nbMax) {
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
			return $this->view->render("voir/braldun/evenements.phtml");
	}

	function renderDistinctions() {
		$tabDistinction = Bral_Util_Distinction::prepareDistinctions($this->view->braldun["id_braldun"]);
		$this->view->tabDistinctions = $tabDistinction["tabDistinctions"];
		return $this->view->render("voir/braldun/distinctions.phtml");
	}

	private function preparePage() {
		$this->_page = 1;
		if (($this->_request->get("caction") == "ask_voir_braldun") && ($this->_request->get("valeur_1") == "f")) {
			$this->_filtre = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_2"));
		} else if (($this->_request->get("caction") == "ask_voir_braldun") && ($this->_request->get("valeur_1") == "p")) { // si le joueur a clique sur une icone
			$this->_page = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_3")) - 1;
			$this->_filtre = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_4"));
		} else if (($this->_request->get("caction") == "ask_voir_braldun") && ($this->_request->get("valeur_1") == "s")) {
			$this->_page = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_3")) + 1;
			$this->_filtre = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_4"));
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
