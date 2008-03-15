<?php

class Bral_Competences_Sequiper extends Bral_Competences_Competence {

	function prepareCommun() {
		Zend_Loader::loadClass("TypeEmplacement");
		Zend_Loader::loadClass("HobbitEquipement");
		Zend_Loader::loadClass("LabanEquipement");
		Zend_Loader::loadClass("EquipementRune");
		
		$this->view->sequiperOk = false;
		$this->equipementPorte = null;
		$this->equipementLaban = null;
		$this->equipementAjoute = null;
		$this->equipementRetire = null;
		
		// on va chercher les emplacements
		$tabTypesEmplacement = null;
		$typeEmplacementTable = new TypeEmplacement();
		$typesEmplacement = $typeEmplacementTable->fetchAll(null, "ordre_emplacement");
		$typesEmplacement = $typesEmplacement->toArray();
		
		foreach ($typesEmplacement as $t) {
			$affiche = "oui";
			$position = "gauche";
			if ($t["nom_systeme_type_emplacement"] == "deuxmains" ||
				$t["nom_systeme_type_emplacement"] == "main" ||
				$t["nom_systeme_type_emplacement"] == "maingauche" ||
				$t["nom_systeme_type_emplacement"] == "maindroite") {
				$affiche = "non";
				$position = "droite";
			}
			
			$tabTypesEmplacement[$t["nom_systeme_type_emplacement"]] = array(
					"nom_type_emplacement" => $t["nom_type_emplacement"],
					"id_type_emplacement" => $t["id_type_emplacement"],
					"ordre_emplacement" => $t["ordre_emplacement"],
					"equipementPorte" => null,
					"equipementLaban" => null,
					"affiche" => $affiche,
					"position" => $position,
			);
		}
		
		// on va chercher l'équipement porté
		$tabEquipementPorte = null;
		$hobbitEquipementTable = new HobbitEquipement();
		$equipementPorteRowset = $hobbitEquipementTable->findByIdHobbit($this->view->user->id_hobbit);
		
		$idEquipements = null;
		foreach ($equipementPorteRowset as $e) {
			$idEquipements[] = $e["id_equipement_hequipement"];
		}
		$equipementRuneTable = new EquipementRune();
		$equipementRunes = $equipementRuneTable->findByIdsEquipement($idEquipements);
		
		$tabWhere = null;
		foreach ($equipementPorteRowset as $e) {
			$this->view->sequiperOk = true;
			$runes = null;
			if (count($equipementRunes) > 0) {
				foreach($equipementRunes as $r) {
					if ($r["id_equipement_rune"] == $e["id_equipement_hequipement"]) {
						$runes[] = array(
						"id_rune_equipement_rune" => $r["id_rune_equipement_rune"],
						"id_fk_type_rune_equipement_rune" => $r["id_fk_type_rune_equipement_rune"],
						"nom_type_rune" => $r["nom_type_rune"],
						"image_type_rune" => $r["image_type_rune"],
						);
					}
				}
			}
				
			$equipement = array(
					"id_equipement" => $e["id_equipement_hequipement"],
					"nom" => $e["nom_type_equipement"],
					"qualite" => $e["nom_type_qualite"],
					"niveau" => $e["niveau_recette_equipement"],
					"id_type_emplacement" => $e["id_type_emplacement"],
					"nom_systeme_type_emplacement" => $e["nom_systeme_type_emplacement"],
					"nb_runes" => $e["nb_runes_hequipement"],
					"id_fk_recette_equipement" => $e["id_fk_recette_hequipement"],
					"armure" => $e["armure_recette_equipement"],
					"force" => $e["force_recette_equipement"],
					"agilite" => $e["agilite_recette_equipement"],
					"vigueur" => $e["vigueur_recette_equipement"],
					"sagesse" => $e["sagesse_recette_equipement"],
					"vue" => $e["vue_recette_equipement"],
					"bm_attaque" => $e["bm_attaque_recette_equipement"],
					"bm_degat" => $e["bm_degat_recette_equipement"],
					"bm_defense" => $e["bm_defense_recette_equipement"],
					"suffixe" => $e["suffixe_mot_runique"],
					"id_fk_mot_runique" => $e["id_fk_mot_runique_hequipement"],
					"runes" => $runes,
			);
			$this->equipementPorte[] = $equipement;
			$tabTypesEmplacement[$e["nom_systeme_type_emplacement"]]["equipementPorte"][] = $equipement;
			$tabTypesEmplacement[$e["nom_systeme_type_emplacement"]]["affiche"] = "oui";
		}
		
		// on va chercher l'équipement présent dans le laban
		$tabEquipementLaban = null;
		$labanEquipementTable = new LabanEquipement();
		$equipementLabanRowset = $labanEquipementTable->findByIdHobbit($this->view->user->id_hobbit);
		
		$tabWhere = null;
		foreach ($equipementLabanRowset as $e) {
			$this->view->sequiperOk = true;
			$equipement = array(
					"id_equipement" => $e["id_laban_equipement"],
					"nom" => $e["nom_type_equipement"],
					"qualite" => $e["nom_type_qualite"],
					"niveau" => $e["niveau_recette_equipement"],
					"id_type_emplacement" => $e["id_type_emplacement"],
					"nom_systeme_type_emplacement" => $e["nom_systeme_type_emplacement"],
					"nb_runes" => $e["nb_runes_laban_equipement"],
					"id_fk_recette_equipement" => $e["id_fk_recette_laban_equipement"],
					"id_fk_mot_runique" => $e["id_fk_mot_runique_laban_equipement"],
			);
			$this->equipementLaban[] = $equipement;
			$tabTypesEmplacement[$e["nom_systeme_type_emplacement"]]["equipementLaban"][] = $equipement;
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
		
		if (((int)$this->request->get("valeur_1").""!=$this->request->get("valeur_1")."")) {
			throw new Zend_Exception(get_class($this)." Equipement invalide : ".$this->request->get("valeur_1"));
		} else {
			$idEquipement = (int)$this->request->get("valeur_1");
		}
		
		// on verifie que l'id equipement est dans l'équipement porté
		$destination = "";
		if ($this->equipementPorte != null) {
			foreach ($this->equipementPorte as $p) {
				if ($p["id_equipement"] == $idEquipement) {
					$destination = "laban";
					$equipement = $p;
					break;
				}
			}
		}
		if ($destination == "" && $this->equipementLaban != null) { // soit dans le laban 
			foreach ($this->equipementLaban as $p) {
				if ($p["id_equipement"] == $idEquipement) {
					$destination = "porte";
					$equipement = $p;
					break;
				}
			}
		}
		
		if ($destination == "") {
			throw new Zend_Exception(get_class($this)." Equipement interdit :" + $idEquipement);
		}
		
		// calcul des jets
		$this->calculSequiper($equipement, $destination);
		$this->view->equipementAjoute = $this->equipementAjoute;
		$this->view->equipementRetire = $this->equipementRetire;
		$this->majEvenementsStandard();
		
		$this->calculPx();
		$this->calculBalanceFaim();
		$this->majHobbit();
	}
	
	private function calculSequiper($equipement, $destination) {
		$mainGauche = true;
		$mainDroite = true;
		$main = true;
		$nbMain = 0;

		if ($destination == "porte") {
			// mettre dans le laban présent à la place de la destination
			if ($this->equipementPorte != null) {
				foreach ($this->equipementPorte as $p) {
					if ($equipement["id_type_emplacement"] == "deuxmains") {
						if ($p["id_type_emplacement"] == "main" || 
							$p["id_type_emplacement"] == "maingauche" || 
							$p["id_type_emplacement"] == "maindroite" || 
							$p["id_type_emplacement"] == "deuxmains") {
							$this->calculTransfertVersLaban($p);
						}
					} else if ($equipement["id_type_emplacement"] == "main") {
						if ($p["id_type_emplacement"] == "maingauche") {
							$mainGauche = false;
							$nbMain = $nbMain + 1;
							$eMainGauche = $p;
						} else if ($p["id_type_emplacement"] == "maindroite") {
							$mainDroite = false;
							$nbMain = $nbMain + 1;
							$eMainDroite = $p;
						} else if ($p["id_type_emplacement"] == "main") {
							$main = false;
							$nbMain = $nbMain + 1;
							$eMain = $p;
						}
					} else if ($equipement["id_type_emplacement"] == $p["id_type_emplacement"]) {
						$this->calculTransfertVersLaban($p);
					}				
				}
				
				if ($equipement["id_type_emplacement"] == "main" ) {
					if ($mainGauche == false && $mainDroite == false && $nbMain >= 2) {
						if ($main == false) {
							$this->calculTranfertVersLaban($eMain);
						} else if ($mainGauche == false) {
							$this->calculTransfertVersLaban($eMainGauche);
						}
					}
				}
			}
			$this->calculTransfertVersEquipement($equipement);
			
			// TODO Calcul des BM attaque, BM Defense, BM Degat, BM armure, BM Force, BM Vigueur, BM Sagesse, 
			// et ARM equipement
		} else { // destination laban
			$this->calculTransfertVersLaban($equipement);
		}
	}
	
	private function calculTransfertVersEquipement($equipement) {
		$this->equipementAjoute[] = $equipement;
		
		$hobbitEquipementTable = new HobbitEquipement();
		$data = array(
			'id_equipement_hequipement' => $equipement["id_equipement"],
			'id_fk_recette_hequipement' => $equipement["id_fk_recette_equipement"],
			'id_fk_hobbit_hequipement' => $this->view->user->id_hobbit,
			'nb_runes_hequipement' => $equipement["nb_runes"],
			'id_fk_mot_runique_hequipement' => $equipement["id_fk_mot_runique"],
		);
		$hobbitEquipementTable->insert($data);
		
		$labanEquipementTable = new LabanEquipement();
		$where = "id_laban_equipement=".$equipement["id_equipement"];
		$labanEquipementTable->delete($where);
	}
	
	private function calculTransfertVersLaban($equipement) {
		$this->equipementRetire[] = $equipement;
		
		$labanEquipementTable = new LabanEquipement();
		$data = array(
			'id_laban_equipement' => $equipement["id_equipement"],
			'id_fk_recette_laban_equipement' => $equipement["id_fk_recette_equipement"],
			'id_fk_hobbit_laban_equipement' => $this->view->user->id_hobbit,
			'nb_runes_laban_equipement' => $equipement["nb_runes"],
			'id_fk_mot_runique_laban_equipement' => $equipement["id_fk_mot_runique"],
		);
		$labanEquipementTable->insert($data);
		
		$hobbitEquipementTable = new HobbitEquipement();
		$where = "id_equipement_hequipement=".$equipement["id_equipement"];
		$hobbitEquipementTable->delete($where);
	}
	
	function getListBoxRefresh() {
		return array("box_profil", "box_equipement", "box_laban", "box_evenements");
	}
}
