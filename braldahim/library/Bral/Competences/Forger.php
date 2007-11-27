<?php

class Bral_Competences_Forger extends Bral_Competences_Competence {

	function prepareCommun() {
		Zend_Loader::loadClass("Echoppe");
		
		$id_type_courant = $this->request->get("type_equipement");
		
		$typeEquipementCourant = null;
		
		// On regarde si le hobbit est dans une de ses echopppes
		$echoppeTable = new Echoppe();
		$echoppes = $echoppeTable->findByCase($this->view->user->x_hobbit, $this->view->user->y_hobbit);
		
		$this->view->forgerEchoppeOk = false;
		if ($echoppes == null || count($echoppes) == 0) {
			$this->view->forgerEchoppeOk = false;
			return;
		}
		$idEchoppe = -1;
		foreach($echoppes as $e) {
			if ($e["id_fk_hobbit_echoppe"] == $this->view->user->id_hobbit && 
				$e["nom_systeme_metier"] == "forgeron" && 
				$e["x_echoppe"] == $this->view->user->x_hobbit && 
				$e["y_echoppe"] == $this->view->user->y_hobbit) {
				$this->view->forgerEchoppeOk = true;
				$idEchoppe = $e["id_echoppe"];
				
				$echoppeCourante = array(
				'id_echoppe' => $e["id_echoppe"],
				'x_echoppe' => $e["x_echoppe"],
				'y_echoppe' => $e["y_echoppe"],
				'id_metier' => $e["id_metier"],
				'quantite_bois_caisse_echoppe' => $e["quantite_bois_caisse_echoppe"],
				'quantite_fourrure_caisse_echoppe' => $e["quantite_fourrure_caisse_echoppe"],
				'quantite_cuir_caisse_echoppe' => $e["quantite_cuir_caisse_echoppe"],
				'quantite_castars_caisse_echoppe' => $e["quantite_castars_caisse_echoppe"],
				);
				break;
			}
		}
		
		if ($this->view->forgerEchoppeOk == false) {
			return;
		}
		
		Zend_Loader::loadClass("HobbitsMetiers");
		$hobbitsMetiersTable = new HobbitsMetiers();
		$hobbitsMetierRowset = $hobbitsMetiersTable->findMetiersByHobbitId($this->view->user->id_hobbit);
		$tabMetierCourant = null;
		foreach($hobbitsMetierRowset as $m) {
			if ($m["est_actif_hmetier"] == "oui") {
				$tabMetierCourant = $m;
				break;
			}
		}
		if ($tabMetierCourant == null) {
			throw new Zend_Exception(get_class($this)." Metier courant inconnu : ");
		}
		
		Zend_Loader::loadClass("TypeEquipement");
		$typeEquipementTable = new TypeEquipement();
		$typeEquipementsRowset = $typeEquipementTable->findByIdMetier($tabMetierCourant["id_metier"]);
		$tabTypeEquipement = null;
		foreach($typeEquipementsRowset as $t) {
			$selected = "";
			if ($id_type_courant == $t["id_type_equipement"]) {
				$selected = "selected";
			}
			$t = array(
			'id_type_equipement' => $t["id_type_equipement"],
			'nom_type_equipement' => $t["nom_type_equipement"],
			'nb_runes_max_type_equipement' => $t["nb_runes_max_type_equipement"],
			'selected' => $selected
			);
			if ($id_type_courant == $t["id_type_equipement"]) {
				$typeEquipementCourant = $t;
			}
			$tabTypeEquipement[] = $t;
		}
		
		$tabNiveaux = null;
		$tabRunes = null;
		$tabCout = null;
		
		if (isset($typeEquipementCourant)) {
			Zend_Loader::loadClass("RecetteCout");
			Zend_Loader::loadClass("RecetteCoutMinerai");
			Zend_Loader::loadClass("EchoppeMinerai");
			
			for ($i = 0; $i <= $this->view->user->niveau_hobbit / 10 ; $i++) {
				$tabNiveaux[$i] = array('niveauText' => 'Niveau '.$i);
			}
			
			$recetteCoutTable = new RecetteCout();
			$recetteCout = $recetteCoutTable->findByIdTypeEquipement($typeEquipementCourant["id_type_equipement"]);
			
			foreach($recetteCout as $r) {
				if ($r["niveau_recette_cout"] <=floor($this->view->user->niveau_hobbit / 10) ) {
					if ($r["cuir_recette_cout"] > 0) {
						$tabCout[$r["niveau_recette_cout"]][] = array("nom"=>"Cuir", "cout" => $r["cuir_recette_cout"]);
					}
					if ($r["fourrure_recette_cout"] > 0) {
						$tabCout[$r["niveau_recette_cout"]][] = array("nom"=>"Fourrure", "cout" => $r["fourrure_recette_cout"]);
					}
					if ($r["bois_recette_cout"] > 0) {
						$tabCout[$r["niveau_recette_cout"]][] = array("nom"=>"Bois", "cout" => $r["bois_recette_cout"]);
					}
				}
			}
			
			$recetteCoutMineraiTable = new RecetteCoutMinerai();
			$recetteCoutMinerai = $recetteCoutMineraiTable->findByIdTypeEquipement($typeEquipementCourant["id_type_equipement"]);
			
			foreach($recetteCoutMinerai as $r) {
			if (($r["quantite_recette_cout_minerai"] > 0) &&
				($r["niveau_recette_cout_minerai"] <=floor($this->view->user->niveau_hobbit / 10))) {
					$tabCout[$r["niveau_recette_cout_minerai"]][] = array("nom"=>$r["nom_type_minerai"], "cout" => $r["quantite_recette_cout_minerai"]);
				}
			}
			
			if ($typeEquipementCourant["nb_runes_max_type_equipement"] > 0) {
				$this->view->peutRunes = true;
				for ($i = 0; $i<=$typeEquipementCourant["nb_runes_max_type_equipement"]; $i++) {
					$tabRunes[] = array('nombre' => $i);
				}
			} else {
				$this->view->peutRunes = false;
			}
			
			$this->view->cout = $tabCout;
			$this->view->niveaux = $tabNiveaux;
			$this->view->runes = $tabRunes;
		}
		
		$this->view->typeEquipement = $tabTypeEquipement;
		$this->idEchoppe = $idEchoppe;
	}

	function prepareFormulaire() {
		if ($this->view->assezDePa == false) {
			return;
		}
	}

	function prepareResultat() {
		Zend_Loader::loadClass("Bral_Util_De");

		// Verification des Pa
		if ($this->view->assezDePa == false) {
			throw new Zend_Exception(get_class($this)." Pas assez de PA : ".$this->view->user->pa_hobbit);
		}

		// Verification chasse
		if ($this->view->forgerOk == false) {
			throw new Zend_Exception(get_class($this)." Forger interdit ");
		}
		
		// calcul des jets
		$this->calculJets();

		if ($this->view->okJet1 === true) {
			$this->calculForger();
			$this->majEvenementsStandard();
		}
		
		$this->calculPx();
		$this->calculBalanceFaim();
		$this->majHobbit();
	}
	
	private function calculForger() {

	}
	
	public function getIdEchoppeCourante() {
		if (isset($this->idEchoppe)) {
			return $this->idEchoppe;
		} else {
			return false;
		}
	}
	
	function getListBoxRefresh() {
		return array("box_profil", "box_vue", "box_competences_metiers", "box_laban", "box_evenements");
	}
}
