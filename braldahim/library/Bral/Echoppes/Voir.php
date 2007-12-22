<?php

class Bral_Echoppes_Voir extends Bral_Echoppes_Echoppe {

	function __construct($nomSystemeAction, $request, $view, $action, $id_echoppe = false) {
		if ($id_echoppe !== false) {
			$this->idEchoppe = $id_echoppe;
		}
		parent::__construct($nomSystemeAction, $request, $view, $action);
	}
	function getNomInterne() {
		return "box_echoppe";
	}
	function render() {
		return $this->view->render("echoppes/voir.phtml");
	}
	
	function prepareCommun() {
		if (!isset($this->idEchoppe)) {
			$id_echoppe = (int)$this->request->get("valeur_1");
		} else {
			$id_echoppe = $this->idEchoppe;
		}

		Zend_Loader::loadClass("Echoppe");
		$echoppeTable = new Echoppe();
		$echoppes = $echoppeTable->findByIdHobbit($this->view->user->id_hobbit);

		$this->view->estSurEchoppe == false;

		$tabEchoppe = null;
		$id_metier = null;
		foreach ($echoppes as $e) {
			if ($e["id_echoppe"] == $id_echoppe) {
				if ($this->view->user->sexe_hobbit == 'feminin') {
					$nom_metier = $e["nom_feminin_metier"];
				} else {
					$nom_metier = $e["nom_masculin_metier"];
				}
				$id_metier = $e["id_metier"];
				$tabEchoppe = array(
				'id_echoppe' => $e["id_echoppe"],
				'x_echoppe' => $e["x_echoppe"],
				'y_echoppe' => $e["y_echoppe"],
				'id_metier' => $e["id_metier"],
				'nom_metier' => $nom_metier,
				'nom_region' => $e["nom_region"],
				'quantite_castar_caisse_echoppe' => $e["quantite_castar_caisse_echoppe"],
				'quantite_rondin_caisse_echoppe' => $e["quantite_rondin_caisse_echoppe"],
				'quantite_peau_caisse_echoppe' => $e["quantite_peau_caisse_echoppe"],
				'quantite_rondin_arriere_echoppe' => $e["quantite_rondin_arriere_echoppe"],
				'quantite_peau_arriere_echoppe' => $e["quantite_peau_arriere_echoppe"],
				'quantite_cuir_arriere_echoppe' => $e["quantite_cuir_arriere_echoppe"],
				'quantite_fourrure_arriere_echoppe' => $e["quantite_fourrure_arriere_echoppe"],
				'quantite_planche_arriere_echoppe' => $e["quantite_planche_arriere_echoppe"],
				);
				if ($this->view->user->x_hobbit == $e["x_echoppe"] &&
				$this->view->user->y_hobbit == $e["y_echoppe"]) {
					$this->view->estSurEchoppe = true;
				}
				break;
			}
		}
		if ($tabEchoppe == null) {
			throw new Zend_Exception(get_class($this)." Echoppe invalide idh:".$this->view->user->id_hobbit." ide:".$id_echoppe);
		}

		Zend_Loader::loadClass("HobbitsCompetences");
		$hobbitsCompetencesTables = new HobbitsCompetences();
		$hobbitCompetences = $hobbitsCompetencesTables->findByIdHobbit($this->view->user->id_hobbit);

		$competence = null;
		foreach($hobbitCompetences as $c) {
			if ($id_metier == $c["id_fk_metier_competence"]) {
				$tabCompetences[] = array("id_competence" => $c["id_fk_competence_hcomp"],
				"nom" => $c["nom_competence"],
				"pa_utilisation" => $c["pa_utilisation_competence"],
				"pourcentage" => $c["pourcentage_hcomp"],
				"nom_systeme" => $c["nom_systeme_competence"]);
			}
		}

		$this->prepareCommunRessources($tabEchoppe["id_echoppe"]);
		$this->prepareCommunEquipements($tabEchoppe["id_echoppe"]);

		$this->view->competences = $tabCompetences;
		$this->view->echoppe = $tabEchoppe;
	}

	function prepareFormulaire() {
	}

	function prepareResultat() {
	}

	function getListBoxRefresh() {
	}

	private function prepareCommunRessources($idEchoppe) {
		Zend_Loader::loadClass("EchoppePartiePlante");
		Zend_Loader::loadClass("EchoppeMinerai");

		$tabPartiePlantes = null;
		$echoppePartiePlanteTable = new EchoppePartieplante();
		$partiePlantes = $echoppePartiePlanteTable->findByIdEchoppe($idEchoppe);

		$this->view->nb_caissePartiePlantes = 0;
		$this->view->nb_arrierePartiePlantes = 0;
		$this->view->nb_prepareePartiePlantes = 0;

		if ($partiePlantes != null) {
			foreach ($partiePlantes as $p) {
				$tabPartiePlantes[] = array(
				"nom_type" => $p["nom_type_partieplante"],
				"nom_plante" => $p["nom_type_plante"],
				"quantite_caisse" => $p["quantite_caisse_echoppe_partieplante"],
				"quantite_arriere" => $p["quantite_arriere_echoppe_partieplante"],
				"quantite_preparee" => $p["quantite_preparees_echoppe_partieplante"],
				);

				$this->view->nb_caissePartiePlantes = $this->view->nb_caissePartiePlantes + $p["quantite_caisse_echoppe_partieplante"];
				$this->view->nb_arrierePartiePlantes = $this->view->nb_arrierePartiePlantes + $p["quantite_arriere_echoppe_partieplante"];
				$this->view->nb_prepareePartiePlantes = $this->view->nb_prepareePartiePlantes  + $p["quantite_preparees_echoppe_partieplante"];
			}
		}

		$tabMinerais = null;
		$echoppeMineraiTable = new EchoppeMinerai();
		$minerais = $echoppeMineraiTable->findByIdEchoppe($idEchoppe);

		$this->view->nb_caisseMinerai = 0;
		$this->view->nb_arriereMinerai = 0;
		$this->view->nb_lingotsMinerai = 0;

		if ($minerais != null) {
			foreach ($minerais as $m) {
				$tabMinerais[] = array(
				"type" => $m["nom_type_minerai"],
				"quantite_caisse" => $m["quantite_caisse_echoppe_minerai"],
				"quantite_arriere" => $m["quantite_arriere_echoppe_minerai"],
				"quantite_lingots" => $m["quantite_lingots_echoppe_minerai"],
				);

				$this->view->nb_caisseMinerai = $this->view->nb_caisseMinerai + $m["quantite_caisse_echoppe_minerai"];
				$this->view->nb_arriereMinerai = $this->view->nb_arriereMinerai + $m["quantite_arriere_echoppe_minerai"];
				$this->view->nb_lingotsMinerai = $this->view->nb_lingotsMinerai  + $m["quantite_lingots_echoppe_minerai"];
			}
		}

		$this->view->partieplantes = $tabPartiePlantes;
		$this->view->minerais = $tabMinerais;
	}

	private function prepareCommunEquipements($idEchoppe) {
		Zend_Loader::loadClass("EchoppeEquipement");

		$tabEquipementsArriereBoutique = null;
		$tabEquipementsEtal = null;
		$echoppeEquipementTable = new EchoppeEquipement();
		$equipements = $echoppeEquipementTable->findByIdEchoppe($idEchoppe);
		
		if (count($equipements) > 0) {
			foreach($equipements as $e) {
				if ($e["type_vente_echoppe_equipement"] == "aucune") {
					$tabEquipementsArriereBoutique[] = array(
					"nom" => $e["nom_type_equipement"],
					"qualite" => $e["nom_type_qualite"],
					"niveau" => $e["niveau_recette_equipement"],
					"nb_runes" => $e["nb_runes_echoppe_equipement"]
					);
				} else {
					$tabEquipementsEtal[] = array(
					"nom" => $e["nom_type_equipement"],
					"qualite" => $e["nom_type_qualite"],
					"niveau" => $e["niveau_recette_equipement"],
					"nb_runes" => $e["nb_runes_echoppe_equipement"]
					);
				}
			}
		}
		$this->view->equipementsArriereBoutique = $tabEquipementsArriereBoutique;
		$this->view->nbEquipementsArriereBoutique = count($tabEquipementsArriereBoutique);
		$this->view->equipementsEtal = $tabEquipementsEtal;
		$this->view->nbEquipementsEtal = count($tabEquipementsEtal);
	}
	
	public function getIdEchoppeCourante() {
		return false;
	}
}