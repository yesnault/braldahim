<?php

class Bral_Competences_Elaborer extends Bral_Competences_Competence {

	function prepareCommun() {
		Zend_Loader::loadClass("Echoppe");

		$id_type_courant = $this->request->get("type_potion");
		$niveau_courant = $this->request->get("niveau_courant");
		
		$typePotionCourante = null;

		// On regarde si le hobbit est dans une de ses echopppes
		$echoppeTable = new Echoppe();
		$echoppes = $echoppeTable->findByCase($this->view->user->x_hobbit, $this->view->user->y_hobbit);

		$this->view->elaborerEchoppeOk = false;
		if ($echoppes == null || count($echoppes) == 0) {
			$this->view->elaborerEchoppeOk = false;
			return;
		}
		
		$idEchoppe = -1;
		foreach($echoppes as $e) {
			if ($e["id_fk_hobbit_echoppe"] == $this->view->user->id_hobbit &&
			$e["nom_systeme_metier"] == "apothicaire" &&
			$e["x_echoppe"] == $this->view->user->x_hobbit &&
			$e["y_echoppe"] == $this->view->user->y_hobbit) {
				$this->view->elaborerEchoppeOk = true;
				$idEchoppe = $e["id_echoppe"];

				$echoppeCourante = array(
				'id_echoppe' => $e["id_echoppe"],
				'x_echoppe' => $e["x_echoppe"],
				'y_echoppe' => $e["y_echoppe"],
				'id_metier' => $e["id_metier"]
				);
				break;
			}
		}

		if ($this->view->elaborerEchoppeOk == false) {
			return;
		}
		
		Zend_Loader::loadClass("TypePotion");
		$typePotionTable = new TypePotion();
		$typePotionRowset = $typePotionTable->fetchall(null, "nom_type_potion");
		$typePotionRowset = $typePotionRowset->toArray();
		$tabTypePotion = null;
		foreach($typePotionRowset as $t) {
			$selected = "";
			if ($id_type_courant == $t["id_type_potion"]) {
				$selected = "selected";
			}
			$t = array(
			'id_type_potion' => $t["id_type_potion"],
			'nom_type_potion' => $t["nom_type_potion"],
			'selected' => $selected
			);
			if ($id_type_courant == $t["id_type_potion"]) {
				$typePotionCourant = $t;
			}
			$tabTypePotion[] = $t;
		}
		
		$tabNiveaux = null;
		$tabCout = null;
		$this->view->ressourcesOk = true;
		$this->view->etape1 = false;
		$this->view->typePotionCourant = null;
		$this->view->cout = null;
		$this->view->niveaux = null;
		$this->view->elaborerPlanteOk = false;

		if (isset($typePotionCourant)) {
			Zend_Loader::loadClass("RecettePotions");
			Zend_Loader::loadClass("EchoppePartieplante");

			$this->view->etape1 = true;

			for ($i = 0; $i <= $this->view->user->niveau_hobbit / 10 ; $i++) {
				$tabNiveaux[$i] = array('niveauText' => 'Niveau '.$i, 'ressourcesOk' => true);
			}
			
			$recettePotionsTable = new RecettePotions();
			$recettePotions = $recettePotionsTable->findByIdTypePotion($typePotionCourant["id_type_potion"]);
			
			Zend_Loader::loadClass("EchoppePartiePlante");
			$tabPartiePlantes = null;
			$echoppePlanteTable = new EchoppePartiePlante();
			$partiesPlantes = $echoppePlanteTable->findByIdEchoppe($idEchoppe);
			
			if ($partiesPlantes != null) {
				foreach ($partiesPlantes as $m) {
					if ($m["quantite_preparees_echoppe_partieplante"] > 1) {
						$tabPartiePlantes[$m["id_fk_type_plante_echoppe_partieplante"]][$m["id_fk_type_echoppe_partieplante"]] = array(
						"nom_type_partieplante" => $m["nom_type_partieplante"],
						"nom_type" => $m["nom_type_plante"],
						"quantite_preparees" => $m["quantite_preparees_echoppe_partieplante"],
						);
						$this->view->elaborerPlanteOk = true;
					}
				}
			}
			
			foreach($tabNiveaux as $k => $v) {
				foreach($recettePotions as $r) {
					$tabCout[$k][] = array("nom_type_plante"=>$r["nom_type_plante"],"nom_type_partieplante"=>$r["nom_type_partieplante"], "cout" => ($r["coef_recette_potion"] + $k));
					if ($r["coef_recette_potion"] + $k > $tabPartiePlantes[$r["id_fk_type_plante_recette_potion"]][$r["id_fk_type_partieplante_recette_potion"]]["quantite_preparees"]) {
						$tabNiveaux[$k]["ressourcesOk"] = false;
					}
				}
			}
				
					/*if ($r["niveau_recette_cout"] <= floor($this->view->user->niveau_hobbit / 10) ) {
					if ($r["cuir_recette_cout"] > 0) {
						$tabCout[$r["niveau_recette_cout"]][] = array("nom"=>"Cuir", "nom_systeme"=>"cuir", "cout" => $r["cuir_recette_cout"]);
						if ($r["cuir_recette_cout"] > $echoppeCourante["quantite_cuir_arriere_echoppe"]) {
							$tabNiveaux[$r["niveau_recette_cout"]]["ressourcesOk"] = false;
						}
					}
					if ($r["fourrure_recette_cout"] > 0) {
						$tabCout[$r["niveau_recette_cout"]][] = array("nom"=>"Fourrure", "nom_systeme"=>"fourrure", "cout" => $r["fourrure_recette_cout"]);
						if ($r["fourrure_recette_cout"] > $echoppeCourante["quantite_fourrure_arriere_echoppe"]) {
							$tabNiveaux[$r["niveau_recette_cout"]]["ressourcesOk"] = false;
						}
					}
					if ($r["planche_recette_cout"] > 0) {
						$tabCout[$r["niveau_recette_cout"]][] = array("nom"=>"Planche", "nom_systeme"=>"planche", "cout" => $r["planche_recette_cout"]);
						if ($r["planche_recette_cout"] > $echoppeCourante["quantite_planche_arriere_echoppe"]) {
							$tabNiveaux[$r["niveau_recette_cout"]]["ressourcesOk"] = false;
						}
					}*/
			$this->view->cout = $tabCout;
			$this->view->niveaux = $tabNiveaux;
			$this->view->typePotionCourant = $typePotionCourant;
		}

		$this->view->typePotion = $tabTypePotion;
		$this->idEchoppe = $idEchoppe;
		$this->echoppeCourante = $echoppeCourante;
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

		// Verification elaborer
		if ($this->view->elaborerEchoppeOk == false) {
			throw new Zend_Exception(get_class($this)." Elaborer Echoppe interdit ");
		}

		// verification ressource  : todo
		$idTypePotion = (int)$this->request->get("valeur_1");
		$niveau = (int)$this->request->get("valeur_2");

		if ($idTypePotion != $this->view->typePotionCourante["id_type_equipement"]) {
			throw new Zend_Exception(get_class($this)." idTypeEqupement interdit A=".$idTypePotion. " B=".$this->view->typePotionCourante["id_type_equipement"]);
		}

		$niveauxOk = false;
		foreach ($this->view->niveaux as $k => $v) {
			if ($k == $niveau && $v["ressourcesOk"] === true) {
				$niveauxOk = true;
			}
		}
		if ($niveauxOk == false) {
			throw new Zend_Exception(get_class($this)." Niveau interdit ");
		}

		// calcul des jets
		$this->calculJets();

		if ($this->view->okJet1 === true) {
			$this->calculElaborer($idTypePotion, $niveau);
			$this->majEvenementsStandard();
		}

		$this->calculPx();
		$this->calculBalanceFaim();
		$this->majHobbit();
	}

	private function calculElaborer($idTypePotion, $niveau) {
		$maitrise = $this->hobbit_competence["pourcentage_hcomp"];
		$chance_a = 11.1-11 * $maitrise;
		$chance_b = 100-(11.1-11 * $maitrise)-(10 * $maitrise);
		$chance_c = 10 * $maitrise;

		$tirage = Bral_Util_De::get_1d100();
		$qualite = -1;
		if ($tirage > 0 && $tirage <= $chance_a) {
			$qualite = 1;
			$this->view->qualite = "m&eacute;diocre";
		} elseif ($tirage > $chance_a && $tirage <= $chance_b) {
			$qualite = 2;
			$this->view->qualite = "standard";
		} elseif ($tirage > $chance_b && $tirage <= 100) {
			$qualite = 3;
			$this->view->qualite = "bonne";
		}
		$this->view->niveau = $niveau;
		
		// TODO
		
		/*Zend_Loader::loadClass("RecetteEquipement");
		$recetteEquipementTable = new RecetteEquipement();
		$recetteEquipement = $recetteEquipementTable->findByIdTypeAndNiveauAndQualite($idTypePotion, $niveau, $qualite);

		if (count($recetteEquipement) > 0) {
			$echoppeMineraiTable = new EchoppeMinerai();

			foreach($this->view->cout[$niveau] as $c) {
				switch ($c["nom_systeme"]) {
					case "cuir" :
						$this->echoppeCourante["quantite_cuir_arriere_echoppe"] = $this->echoppeCourante["quantite_cuir_arriere_echoppe"] - $c["cout"];
						if ($this->echoppeCourante["quantite_cuir_arriere_echoppe"] < 0) {
							$this->echoppeCourante["quantite_cuir_arriere_echoppe"] = 0;
						}
						break;
					case "fourrure" :
						$this->echoppeCourante["quantite_fourrure_arriere_echoppe"] = $this->echoppeCourante["quantite_fourrure_arriere_echoppe"] - $c["cout"];
						if ($this->echoppeCourante["quantite_fourrure_arriere_echoppe"] < 0) {
							$this->echoppeCourante["quantite_fourrure_arriere_echoppe"] = 0;
						}
						break;
					case "planche" :
						$this->echoppeCourante["quantite_planche_arriere_echoppe"] = $this->echoppeCourante["quantite_planche_arriere_echoppe"] - $c["cout"];
						if ($this->echoppeCourante["quantite_planche_arriere_echoppe"] < 0) {
							$this->echoppeCourante["quantite_planche_arriere_echoppe"] = 0;
						}
						break;
					default :
						if (!isset($c["id_type_minerai"])) {
							throw new Zend_Exception(get_class($this)." Minerai inconnu ".$c["nom_systeme"]);
						}
						foreach($this->echoppeMinerai as $m) {
							if ($m["id_fk_type_echoppe_minerai"] == $c["id_type_minerai"]) {
								$quantite = $m["quantite_lingots_echoppe_minerai"] - $c["cout"];
								if ($quantite < 0) {
									$quantite = 0;
								}
								$data = array('quantite_lingots_echoppe_minerai' => $quantite);
								$where = 'id_fk_type_echoppe_minerai = '. $c["id_type_minerai"];
								$where .= ' AND id_fk_echoppe_echoppe_minerai='.$this->echoppeCourante["id_echoppe"];
								$echoppeMineraiTable->update($data, $where);
							}
						}
				}
			}

			Zend_Loader::loadClass("Echoppe");
			$echoppeTable = new Echoppe();
			$data = array(
			'quantite_cuir_arriere_echoppe' => $this->echoppeCourante["quantite_cuir_arriere_echoppe"],
			'quantite_fourrure_arriere_echoppe' => $this->echoppeCourante["quantite_fourrure_arriere_echoppe"],
			'quantite_planche_arriere_echoppe' => $this->echoppeCourante["quantite_planche_arriere_echoppe"],
			);
			$echoppeTable->update($data, 'id_echoppe = '.$this->echoppeCourante["id_echoppe"]);
				
			
			foreach($recetteEquipement as $r) {
				$id_fk_recette_equipement = $r["id_recette_equipement"];
				break;
			}

			Zend_Loader::loadClass("EchoppeEquipement");
			$echoppeEquipementTable = new EchoppeEquipement();
			$data = array(
			'id_fk_echoppe_echoppe_equipement' => $this->idEchoppe,
			'id_fk_recette_echoppe_equipement' => $id_fk_recette_equipement,
			'type_vente_echoppe_equipement' => 'aucune',
			);
			$echoppeEquipementTable->insert($data);

		} else {
			throw new Zend_Exception(get_class($this)." Recette inconnue: id=".$idTypePotion." n=".$niveau. " q=".$qualite);
				
		}
*/
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
