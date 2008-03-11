<?php

class Bral_Competences_Sequiper extends Bral_Competences_Competence {

	function prepareCommun() {
		Zend_Loader::loadClass("TypeEmplacement");
		Zend_Loader::loadClass("HobbitEquipement");
		Zend_Loader::loadClass("LabanEquipement");
		
		$this->view->sequiperOk = false;
		
		// on va chercher les emplacements
		$tabTypesEmplacement = null;
		$typeEmplacementTable = new TypeEmplacement();
		$typesEmplacement = $typeEmplacementTable->fetchAll(null, "ordre_emplacement");
		$typesEmplacement = $typesEmplacement->toArray();
		
		foreach ($typesEmplacement as $t) {
			$tabTypesEmplacement[$t["id_type_emplacement"]] = array(
					"nom_type_emplacement" => $t["nom_type_emplacement"],
					"ordre_emplacement" => $t["ordre_emplacement"],
					"equipementPorte" => null,
					"equipementLaban" => null,
			);
		}
		
		// on va chercher l'équipement porté
		$tabEquipementPorte = null;
		$hobbitEquipementTable = new HobbitEquipement();
		$equipementPorte = $hobbitEquipementTable->findByIdHobbit($this->view->user->id_hobbit);
		
		$tabWhere = null;
		foreach ($equipementPorte as $e) {
			$this->view->sequiperOk = true;
			$tabTypesEmplacement[$e["id_type_emplacement"]]["equipementPorte"][] = array(
					"id_equipement" => $e["id_equipement_hequipement"],
					"nom" => $e["nom_type_equipement"],
					"qualite" => $e["nom_type_qualite"],
					"niveau" => $e["niveau_recette_equipement"],
					"id_type_emplacement" => $e["id_type_emplacement"]
			);
		}
		
		// on va chercher l'équipement présent dans le laban
		$tabEquipementLaban = null;
		$labanEquipementTable = new LabanEquipement();
		$equipementLaban = $labanEquipementTable->findByIdHobbit($this->view->user->id_hobbit);
		
		$tabWhere = null;
		foreach ($equipementLaban as $e) {
			$this->view->sequiperOk = true;
			$tabTypesEmplacement[$e["id_type_emplacement"]]["equipementLaban"][] = array(
					"id_equipement" => $e["id_laban_equipement"],
					"nom" => $e["nom_type_equipement"],
					"qualite" => $e["nom_type_qualite"],
					"niveau" => $e["niveau_recette_equipement"],
					"id_type_emplacement" => $e["id_type_emplacement"],
			);
		}
		
		$this->view->typesEmplacement = $tabTypesEmplacement;
		$this->view->nbTypesEmplacement = count($tabTypesEmplacement);
		
	}

	function prepareFormulaire() {
		if ($this->view->assezDePa == false) {
			return;
		}
	}

	function prepareResultat() {
		Zend_Loader::loadClass("Bral_Util_De");
		Zend_Loader::loadClass('Hobbit');

		// Verification des Pa
		if ($this->view->assezDePa == false) {
			throw new Zend_Exception(get_class($this)." Pas assez de PA : ".$this->view->user->pa_hobbit);
		}

		// Verification sequiper
		if ($this->view->sequiperOk == false) {
			throw new Zend_Exception(get_class($this)." Sequiper interdit ");
		}
		
		// on verifie que l'id equipement est soit dans le laban, soit dans l'équipement porté
		
		// calcul des jets
		$this->calculJets();

		if ($this->view->okJet1 === true) {
			$this->calculSequiper();
			$this->majEvenementsStandard();
		}
		
		$this->calculPx();
		$this->calculBalanceFaim();
		$this->majHobbit();
	}
	
	private function calculTranfertVersEquipement($equipement) {
		
		$hobbitEquipementTable = new HobbitEquipement();
		$data = array(
			'id_equipement_hequipement' => $equipement["id_laban_equipement"],
			'id_fk_recette_hequipement' => $equipement["id_fk_recette_laban_equipement"],
			'id_fk_hobbit_hequipement' => $this->view->user->id_hobbit,
			'nb_runes_hequipement' => $equipement["nb_runes"],
		);
		$hobbitEquipementTable->insert($data);
		
		$hobbitEquipementTable = new HobbitEquipement();
		$where = "id_echoppe_equipement=".$equipement["id_echoppe_equipement"];
		$hobbitEquipementTable->delete($where);
	}
	
	private function calculTranfertVersLaban($equipement) {
		
		$labanEquipementTable = new LabanEquipement();
		$data = array(
			'id_laban_equipement' => $equipement["id_echoppe_equipement"],
			'id_fk_recette_laban_equipement' => $equipement["id_fk_recette_echoppe_equipement"],
			'id_fk_hobbit_laban_equipement' => $this->view->user->id_hobbit,
			'nb_runes_laban_equipement' => $equipement["nb_runes"],
		);
		$labanEquipementTable->insert($data);
		
		$echoppeEquipementTable = new EchoppeEquipement();
		$where = "id_echoppe_equipement=".$equipement["id_echoppe_equipement"];
		$echoppeEquipementTable->delete($where);
	}
	
	function getListBoxRefresh() {
		return array("box_profil", "box_equipement", "box_laban", "box_evenements");
	}
}
