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
		
		$tabMetier["tabMetierCourant"] = null;
		$tabMetier["tabMetiers"] = null;
		$tabMetier["possedeMetier"] = false;
		
		$hobbitTable = new Hobbit();
		$hobbitRowset = $hobbitTable->findById(Bral_Util_Controle::getValeurIntVerif($this->_request->get("hobbit")));
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
			$hobbit = null;
		}
		
		Zend_Loader::loadClass("Bral_Util_Equipement");
		$tabEmplacementsEquipement = Bral_Util_Equipement::getTabEmplacementsEquipement($this->view->hobbit["id_hobbit"]);
		$this->view->tabTypesEmplacement = $tabEmplacementsEquipement["tabTypesEmplacement"];
		
		$this->view->tabMetierCourant = $tabMetier["tabMetierCourant"];
		$this->view->tabMetiers = $tabMetier["tabMetiers"];
		$this->view->possedeMetier = $tabMetier["possedeMetier"];
		$this->view->tabTitres = $tabTitre["tabTitres"];
		
		if ($this->_request->get("menu") == "evenements" && $this->view->connu != null) {
			return $this->renderEvenements();
		} else { 
			return $this->view->render("voir/hobbit.phtml");
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
			$this->_filtre =  Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_2"));
		} else if (($this->_request->get("caction") == "ask_voir_hobbit") && ($this->_request->get("valeur_1") == "p")) { // si le joueur a clique sur une icone
			$this->_page =  Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_3")) - 1;
			$this->_filtre =  Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_4"));
		} else if (($this->_request->get("caction") == "ask_voir_hobbit") && ($this->_request->get("valeur_1") == "s")) {
			$this->_page =  Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_3")) + 1;
			$this->_filtre =  Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_4"));
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
