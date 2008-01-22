<?php

class Bral_Competences_Elaborer extends Bral_Competences_Competence {

	function prepareCommun() {
		Zend_Loader::loadClass("Echoppe");

		$id_type_courant = $this->request->get("type_equipement");

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
				'id_metier' => $e["id_metier"],
				'quantite_planche_arriere_echoppe' => $e["quantite_planche_arriere_echoppe"],
				'quantite_fourrure_arriere_echoppe' => $e["quantite_fourrure_arriere_echoppe"],
				'quantite_cuir_arriere_echoppe' => $e["quantite_cuir_arriere_echoppe"],
				);
				break;
			}
		}

		if ($this->view->elaborerEchoppeOk == false) {
			return;
		}

		$this->view->typePotion = $tabTypeEquipement;
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
*/
		} else {
			throw new Zend_Exception(get_class($this)." Recette inconnue: id=".$idTypePotion." n=".$niveau. " q=".$qualite);
				
		}
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
