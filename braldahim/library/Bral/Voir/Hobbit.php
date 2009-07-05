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
class Bral_Voir_Hobbit {

	function __construct($request, $view) {
		Zend_Loader::loadClass("Evenement");
		Zend_Loader::loadClass("TypeEvenement");
		Zend_Loader::loadClass("HobbitsTitres");
		Zend_Loader::loadClass("Communaute");
		Zend_Loader::loadClass("Bral_Util_Metier");
		Zend_Loader::loadClass("Bral_Util_Titre");
		Zend_Loader::loadClass("Bral_Helper_ProfilEquipement");
		Zend_Loader::loadClass('AncienHobbit');
		Zend_Loader::loadClass("Charrette");

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
		$this->view->ancien = false;
		$this->view->connu = false;
		$this->view->hobbit = null;
		$this->view->communaute = null;

		$tabMetier["tabMetierCourant"] = null;
		$tabMetier["tabMetiers"] = null;
		$tabMetier["possedeMetier"] = false;
		$tabTitre["tabTitres"] = null;

		$val = $this->_request->get("hobbit");
		if ($val != "" && ((int)$val."" == $val."")) {
			return $this->renderData();
		} else {
			$this->view->flux = $this->view->render("voir/hobbit/profil.phtml");;
			return $this->view->render("voir/hobbit.phtml");
		}
	}

	private function renderData() {
		$hobbitTable = new Hobbit();
		$idHobbit = Bral_Util_Controle::getValeurIntVerif($this->_request->get("hobbit"));
		$hobbitRowset = $hobbitTable->findById($idHobbit);
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
			$tabMetier = Bral_Util_Metier::prepareMetier($this->view->hobbit["id_hobbit"], $this->view->hobbit["sexe_hobbit"]);
			$tabTitre = Bral_Util_Titre::prepareTitre($this->view->hobbit["id_hobbit"], $this->view->hobbit["sexe_hobbit"]);
		} else {
			$this->rechercheAncien($idHobbit);
		}

		if ($this->view->ancien == false) {
			Zend_Loader::loadClass("Bral_Util_Equipement");
			$tabEmplacementsEquipement = Bral_Util_Equipement::getTabEmplacementsEquipement($this->view->hobbit["id_hobbit"]);
			$this->view->tabTypesEmplacement = $tabEmplacementsEquipement["tabTypesEmplacement"];

			$this->view->tabMetierCourant = $tabMetier["tabMetierCourant"];
			$this->view->tabMetiers = $tabMetier["tabMetiers"];
			$this->view->possedeMetier = $tabMetier["possedeMetier"];
			$this->view->tabTitres = $tabTitre["tabTitres"];

			$charretteTable = new Charrette();
			$nbCharrette = $charretteTable->countByIdHobbit($this->view->hobbit["id_hobbit"]);
			if ($nbCharrette > 0) {
				$this->view->possedeCharrette = "oui";
			} else {
				$this->view->possedeCharrette = "non";
			}
		}

		if ($this->_request->get("menu") == "evenements" && $this->view->connu != null && $this->view->ancien == false) {
			return $this->renderEvenements();
		} else if ($this->_request->get("menu") == "famille" && $this->view->connu != null) {
			return $this->renderFamille();
		} else {
			if ($this->_request->get("direct") == "evenements") {
				$flux = $this->renderEvenements();
			} else if ($this->_request->get("direct") == "famille") {
				$flux = $this->renderFamille();
			} else {
				$flux = $this->view->render("voir/hobbit/profil.phtml");
			}
			$this->view->flux = $flux;
			return $this->view->render("voir/hobbit.phtml");
		}
	}

	private function rechercheAncien($idHobbit) {
		$ancienHobbitTable = new AncienHobbit();
		$hobbit = $ancienHobbitTable->findById($idHobbit);
		if ($hobbit != null) {
			$this->view->connu = true;
			$this->view->ancien = true;
			$this->view->hobbit["id_hobbit"] = $hobbit["id_hobbit_ancien_hobbit"];

			$this->view->hobbit["nom_hobbit"] = $hobbit["nom_ancien_hobbit"];
			$this->view->hobbit["prenom_hobbit"] = $hobbit["prenom_ancien_hobbit"];
			$this->view->hobbit["id_fk_nom_initial_hobbit"] = $hobbit["id_fk_nom_initial_ancien_hobbit"];
			$this->view->hobbit["email_hobbit"] = $hobbit["email_ancien_hobbit"];
			$this->view->hobbit["sexe_hobbit"] = $hobbit["sexe_ancien_hobbit"];
			$this->view->hobbit["niveau_hobbit"] = $hobbit["niveau_ancien_hobbit"];
			$this->view->hobbit["nb_ko_hobbit"] = $hobbit["nb_ko_ancien_hobbit"];
			$this->view->hobbit["nb_hobbit_ko_hobbit"] = $hobbit["nb_hobbit_ko_ancien_hobbit"];
			$this->view->hobbit["nb_plaque_hobbit"] = $hobbit["nb_plaque_ancien_hobbit"];
			$this->view->hobbit["nb_hobbit_plaquage_hobbit"] = $hobbit["nb_hobbit_plaquage_ancien_hobbit"];
			$this->view->hobbit["nb_monstre_kill_hobbit"] = $hobbit["nb_monstre_kill_ancien_hobbit"];
			$this->view->hobbit["id_fk_mere_hobbit"] = $hobbit["id_fk_mere_ancien_hobbit"];
			$this->view->hobbit["id_fk_pere_hobbit"] = $hobbit["id_fk_pere_ancien_hobbit"];
			$this->view->hobbit["metiers_ancien_hobbit"] = $hobbit["metiers_ancien_hobbit"];
			$this->view->hobbit["titres_ancien_hobbit"] = $hobbit["titres_ancien_hobbit"];
			$this->view->hobbit["date_creation_hobbit"] = $hobbit["date_creation_ancien_hobbit"];
		}
	}

	private function prepareMetier() {
		Zend_Loader::loadClass("HobbitsMetiers");
		$hobbitsMetiersTable = new HobbitsMetiers();
		$hobbitsMetierRowset = $hobbitsMetiersTable->findMetiersByHobbitId($this->view->user->id_hobbit);
		unset($hobbitsMetiersTable);
		$tabMetiers = null;
		$tabMetierCourant = null;
		$possedeMetier = false;

		foreach($hobbitsMetierRowset as $m) {
			$possedeMetier = true;

			if ($this->view->user->sexe_hobbit == 'feminin') {
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
		unset($hobbitsMetierRowset);

		$this->view->tabMetierCourant = $tabMetierCourant;
		$this->view->tabMetiers = $tabMetiers;
		$this->view->possedeMetier = $possedeMetier;
		$this->view->nom_interne = $this->getNomInterne();
	}


	function renderFamille() {

		Zend_Loader::loadClass('Couple');
		$hobbitTable = new Hobbit();
		$ancienHobbitTable = new AncienHobbit();

		$this->view->pereMereOk = false;
		$pere = null;
		$mere = null;

		$this->view->mereAncienne = false;
		$this->view->pereAncien = false;

		if ($this->view->hobbit["id_fk_mere_hobbit"] != null && $this->view->hobbit["id_fk_pere_hobbit"] != null &&
		$this->view->hobbit["id_fk_mere_hobbit"] != 0 && $this->view->hobbit["id_fk_pere_hobbit"] != 0 ) {

			$pere = $hobbitTable->findById($this->view->hobbit["id_fk_pere_hobbit"]);
			$mere = $hobbitTable->findById($this->view->hobbit["id_fk_mere_hobbit"]);

			if ($pere == null) {
				$this->view->pereAncien = true;
				$pere = $ancienHobbitTable->findById($this->view->hobbit["id_fk_pere_hobbit"]);
			}

			if ($mere == null) {
				$this->view->mereAncienne = true;
				$mere = $ancienHobbitTable->findById($this->view->hobbit["id_fk_mere_hobbit"]);
			}

			$this->view->pereMereOk = true;
		}

		$this->view->pere = $pere;
		$this->view->mere = $mere;

		// on regarde s'il y a des enfants
		$enfants = null;
		$enfantsRowset = $hobbitTable->findEnfants($this->view->hobbit["sexe_hobbit"], $this->view->hobbit["id_hobbit"]);
		unset($hobbitTable);
		$this->view->nbEnfants = count($enfantsRowset);

		if (count($this->view->nbEnfants) > 0) {
			foreach($enfantsRowset as $e) {
				$enfants[] = array("prenom" => $e["prenom_hobbit"],
									"nom" => $e["nom_hobbit"],
									"id_hobbit" => $e["id_hobbit"],
									"sexe_hobbit" => $e["sexe_hobbit"],
									"date_naissance" => $e["date_creation_hobbit"]);
			}
			unset($enfantsRowset);
		}
		$this->view->enfants = $enfants;

		// on va chercher les informations du conjoint
		Zend_Loader::loadClass("Bral_Util_Conjoints");
		$this->view->conjoint = Bral_Util_Conjoints::getConjoint($this->view->hobbit["sexe_hobbit"], $this->view->hobbit["id_hobbit"]);

		$this->view->dateNaissance = Bral_Util_ConvertDate::get_datetime_mysql_datetime('d/m/y \&\a\g\r\a\v\e; H:i:s',$this->view->hobbit["date_creation_hobbit"]);
		return $this->view->render("voir/hobbit/famille.phtml");
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
			return $this->view->render("voir/hobbit/evenements.phtml");
	}

	private function preparePage() {
		$this->_page = 1;
		if (($this->_request->get("caction") == "ask_voir_hobbit") && ($this->_request->get("valeur_1") == "f")) {
			$this->_filtre = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_2"));
		} else if (($this->_request->get("caction") == "ask_voir_hobbit") && ($this->_request->get("valeur_1") == "p")) { // si le joueur a clique sur une icone
			$this->_page = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_3")) - 1;
			$this->_filtre = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_4"));
		} else if (($this->_request->get("caction") == "ask_voir_hobbit") && ($this->_request->get("valeur_1") == "s")) {
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
