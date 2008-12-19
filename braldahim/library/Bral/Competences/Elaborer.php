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
				'selected' => $selected,
				'bm_type_potion' => $t["bm_type_potion"],
				'caract_type_potion' => $t["caract_type_potion"],
			);
			if ($id_type_courant == $t["id_type_potion"]) {
				$typePotionCourante = $t;
			}
			$tabTypePotion[] = $t;
		}
		
		$tabNiveaux = null;
		$tabCout = null;
		$this->view->ressourcesOk = true;
		$this->view->etape1 = false;
		$this->view->typePotionCourante = null;
		$this->view->cout = null;
		$this->view->niveaux = null;
		$this->view->elaborerPlanteOk = false;

		if (isset($typePotionCourante)) {
			Zend_Loader::loadClass("RecettePotions");
			Zend_Loader::loadClass("EchoppePartieplante");

			$this->view->etape1 = true;

			for ($i = 0; $i <= $this->view->user->niveau_hobbit / 10 ; $i++) {
				$tabNiveaux[$i] = array('niveauText' => 'Niveau '.$i, 'ressourcesOk' => true);
			}
			
			$recettePotionsTable = new RecettePotions();
			$recettePotions = $recettePotionsTable->findByIdTypePotion($typePotionCourante["id_type_potion"]);
			
			Zend_Loader::loadClass("EchoppePartieplante");
			$tabPartiePlantes = null;
			$echoppePlanteTable = new EchoppePartieplante();
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
					$tabCout[$k][] = array(
						"nom_type_plante"=>$r["nom_type_plante"], 
						"id_type_plante"=>$r["id_type_plante"], 
						"nom_type_partieplante"=>$r["nom_type_partieplante"], 
						"id_type_partieplante"=>$r["id_type_partieplante"], 
						"cout" => ($r["coef_recette_potion"] + $k),
					);
					if (isset($tabPartiePlantes[$r["id_fk_type_plante_recette_potion"]]) && (isset($tabPartiePlantes[$r["id_fk_type_plante_recette_potion"]][$r["id_fk_type_partieplante_recette_potion"]]["quantite_preparees"])) ) {
						if ($r["coef_recette_potion"] + $k > $tabPartiePlantes[$r["id_fk_type_plante_recette_potion"]][$r["id_fk_type_partieplante_recette_potion"]]["quantite_preparees"]) {
							$tabNiveaux[$k]["ressourcesOk"] = false;
						}
					} else {
						$tabNiveaux[$k]["ressourcesOk"] = false;
					}
				}
			}
				
			$this->view->cout = $tabCout;
			$this->view->niveaux = $tabNiveaux;
			$this->view->typePotionCourante = $typePotionCourante;
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
		// Verification des Pa
		if ($this->view->assezDePa == false) {
			throw new Zend_Exception(get_class($this)." Pas assez de PA : ".$this->view->user->pa_hobbit);
		}

		// Verification elaborer
		if ($this->view->elaborerEchoppeOk == false) {
			throw new Zend_Exception(get_class($this)." Elaborer Echoppe interdit ");
		}

		// verification ressources
		$idTypePotion = (int)$this->request->get("valeur_1");
		$niveau = (int)$this->request->get("valeur_2");

		if ($idTypePotion != $this->view->typePotionCourante["id_type_potion"]) {
			throw new Zend_Exception(get_class($this)." idTypePotion interdit A=".$idTypePotion. " B=".$this->view->typePotionCourante["id_type_potion"]);
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
		} else { // Jet Raté
			$this->calculRateElaborer($niveau);
		}

		$this->calculPx();
		$this->calculBalanceFaim();
		$this->majHobbit();
	}
	
	private function calculRateElaborer($niveau) {
		Zend_Loader::loadClass("EchoppePartieplante");
		$echoppePartiePlanteTable = new EchoppePartieplante();
		
		foreach ($this->view->cout[$niveau] as $c) {
			$data = array('quantite_preparees_echoppe_partieplante' => -intval($c["cout"]/2),
						  'id_fk_type_echoppe_partieplante' => $c["id_type_partieplante"],
						  'id_fk_type_plante_echoppe_partieplante' => $c["id_type_plante"],
						  'id_fk_echoppe_echoppe_partieplante' => $this->idEchoppe);
			$echoppePartiePlanteTable->insertOrUpdate($data);
		}
	}
	
	private function calculElaborer($idTypePotion, $niveau) {
		$this->view->effetRune = false;
		
		$maitrise = $this->hobbit_competence["pourcentage_hcomp"];
		
		if (Bral_Util_Commun::isRunePortee($this->view->user->id_hobbit, "AP")) { // s'il possede une rune AP
			$this->view->effetRune = true;
			$chance_a = 0;
			$chance_b = 100-(10 * $maitrise);
			$chance_c = 10 * $maitrise;
		} else {
			$chance_a = 11.1-11 * $maitrise;
			$chance_b = 100-(11.1-11 * $maitrise)-(10 * $maitrise);
			$chance_c = 10 * $maitrise;
		}
		
		/*
		 * Seul le meilleur des n jets est gardé. n=(BM SAG/2)+1.
		 */
		$n = (($this->view->user->sagesse_bm_hobbit + $this->view->user->sagesse_bbdf_hobbit) / 2 ) + 1;
		
		if ($n < 1) $n = 1;
		
		$tirage = 0;
		
		for ($i = 1; $i <= $n; $i ++) {
			$tirageTemp = Bral_Util_De::get_1d100();
			if ($tirageTemp > $tirage) {
				$tirage = $tirageTemp;
			}
		}
		
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
		$this->view->niveauQualite = $qualite;
		
		Zend_Loader::loadClass("EchoppePartieplante");
		$echoppePartiePlanteTable = new EchoppePartieplante();
		
		foreach ($this->view->cout[$niveau] as $c) {
			$data = array('quantite_preparees_echoppe_partieplante' => -$c["cout"],
						  'id_fk_type_echoppe_partieplante' => $c["id_type_partieplante"],
						  'id_fk_type_plante_echoppe_partieplante' => $c["id_type_plante"],
						  'id_fk_echoppe_echoppe_partieplante' => $this->idEchoppe);
			$echoppePartiePlanteTable->insertOrUpdate($data);
		}
		
		Zend_Loader::loadClass("EchoppePotion");
		$echoppePotionTable = new EchoppePotion();
		$data = array(
			'id_fk_echoppe_echoppe_potion' => $this->idEchoppe,
			'id_fk_type_potion_echoppe_potion' => $idTypePotion,
			'type_vente_echoppe_potion' => 'aucune',
			'id_fk_type_qualite_echoppe_potion' => $qualite,
			'niveau_echoppe_potion' => $niveau,
		);
		$echoppePotionTable->insert($data);
	}
	
	// Gain : [(nivP+1)/(nivH+1)+1+NivQ]*10 PX
	public function calculPx() {
		$this->view->nb_px_commun = 0;
		$this->view->calcul_px_generique = true;
		if ($this->view->okJet1 === true) {
			$this->view->nb_px_perso = floor((($this->view->niveau +1)/(floor($this->view->user->niveau_hobbit/10) + 1) + 1 + ($this->view->niveauQualite - 1) )*10);
		} else {
			$this->view->nb_px_perso = 0;
		}
		$this->view->nb_px = $this->view->nb_px_perso + $this->view->nb_px_commun;
	}	

	public function getIdEchoppeCourante() {
		if (isset($this->idEchoppe)) {
			return $this->idEchoppe;
		} else {
			return false;
		}
	}
	
	function getListBoxRefresh() {
		return $this->constructListBoxRefresh(array("box_competences_metiers", "box_echoppes"));
	}
}
