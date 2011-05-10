<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
abstract class Bral_Competences_Produire extends Bral_Competences_Competence {

	function prepareCommun() {
		Zend_Loader::loadClass("Echoppe");
		Zend_Loader::loadClass("TypeMateriel");
		Zend_Loader::loadClass("Bral_Helper_DetailMateriel");

		$id_type_courant = $this->request->get("type_materiel");

		$typeMaterielCourant = null;

		// On regarde si le Braldûn est dans une de ses echopppes
		$echoppeTable = new Echoppe();
		$echoppes = $echoppeTable->findByCase($this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun);

		$this->view->produireEchoppeOk = false;
		if ($echoppes == null || count($echoppes) == 0) {
			$this->view->produireEchoppeOk = false;
			return;
		}

		$idEchoppe = -1;
		$metier = substr($this->nom_systeme, 8, strlen($this->nom_systeme) - 8);
		foreach($echoppes as $e) {
			if ($e["id_fk_braldun_echoppe"] == $this->view->user->id_braldun &&
			$e["nom_systeme_metier"] == $metier &&
			$e["x_echoppe"] == $this->view->user->x_braldun &&
			$e["y_echoppe"] == $this->view->user->y_braldun &&
			$e["z_echoppe"] == $this->view->user->z_braldun) {
				$this->view->produireEchoppeOk = true;
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

		if ($this->view->produireEchoppeOk == false) {
			return;
		}

		Zend_Loader::loadClass("TypeMateriel");
		$typeMaterielTable = new TypeMateriel();
		$typeMaterielsRowset = $typeMaterielTable->findByIdMetier($this->getIdMetier());
		$tabTypeMateriel = null;
		foreach($typeMaterielsRowset as $t) {
			$selected = "";
			if ($id_type_courant == $t["id_type_materiel"]) {
				$selected = "selected";
			}
			$t = array(
				'id_type_materiel' => $t["id_type_materiel"],
				'nom_type_materiel' =>$t["nom_type_materiel"],
				'capacite' => $t["capacite_type_materiel"], 
				'durabilite' => $t["durabilite_type_materiel"], 
				'usure' => $t["usure_type_materiel"], 
				'poids' => $t["poids_type_materiel"], 
				'selected' => $selected,
				'nom_systeme_type_materiel' => $t["nom_systeme_type_materiel"],
				'durabilite_type_materiel' => $t["durabilite_type_materiel"],
				'durabilite_type_materiel' => $t["durabilite_type_materiel"],
				'capacite_type_materiel' => $t["capacite_type_materiel"],
			);
			if ($id_type_courant == $t["id_type_materiel"]) {
				$typeMaterielCourant = $t;
			}
			$tabTypeMateriel[] = $t;
		}

		$tabCout = null;
		$this->view->ressourcesOk = true;
		$this->view->etape1 = false;
		$this->view->typeMaterielCourant = null;
		$this->view->cout = null;

		if (isset($typeMaterielCourant)) {
			Zend_Loader::loadClass("RecetteMaterielCout");
			Zend_Loader::loadClass("RecetteMaterielCoutMinerai");
			Zend_Loader::loadClass("RecetteMaterielCoutPlante");
			Zend_Loader::loadClass("EchoppeMinerai");
			Zend_Loader::loadClass("EchoppePartieplante");

			$this->view->etape1 = true;
			$ressourcesOk = true;

			$recetteCoutTable = new RecetteMaterielCout();
			$recetteCout = $recetteCoutTable->findByIdTypeMateriel($typeMaterielCourant["id_type_materiel"]);

			foreach($recetteCout as $r) {
				if ($r["cuir_recette_materiel_cout"] > 0) {
					$ok = "oui";
					if ($r["cuir_recette_materiel_cout"] > $echoppeCourante["quantite_cuir_arriere_echoppe"]) {
						$ressourcesOk = false;
						$ok = "non";
					}
					$tabCout[] = array("nom" => "Cuir", "nom_systeme"=>"cuir", "cout" => $r["cuir_recette_materiel_cout"], "ressourcesOk" => $ok);

				}
				if ($r["fourrure_recette_materiel_cout"] > 0) {
					$ok = "oui";
					if ($r["fourrure_recette_materiel_cout"] > $echoppeCourante["quantite_fourrure_arriere_echoppe"]) {
						$ressourcesOk = false;
						$ok = "non";
					}
					$tabCout[] = array("nom" => "Fourrure", "nom_systeme"=>"fourrure", "cout" => $r["fourrure_recette_materiel_cout"], "ressourcesOk" => $ok);

				}
				if ($r["planche_recette_materiel_cout"] > 0) {
					if ($r["planche_recette_materiel_cout"] > 1) {
						$nom = "Planches";
					} else {
						$nom = "Planche";
					}
					$ok = "oui";
					if ($r["planche_recette_materiel_cout"] > $echoppeCourante["quantite_planche_arriere_echoppe"]) {
						$ressourcesOk = false;
						$ok = "non";
					}
					$tabCout[] = array("nom" => $nom, "nom_systeme"=>"planche", "cout" => $r["planche_recette_materiel_cout"], "ressourcesOk" => $ok);
				}
			}

			$recetteCoutMineraiTable = new RecetteMaterielCoutMinerai();
			$recetteCoutMinerai = $recetteCoutMineraiTable->findByIdTypeMateriel($typeMaterielCourant["id_type_materiel"]);

			$echoppeMineraiTable = new EchoppeMinerai();
			$this->echoppeMinerai = $echoppeMineraiTable->findByIdEchoppe($idEchoppe);

			foreach($recetteCoutMinerai as $r) {
				if ($r["quantite_lingot_recette_materiel_cout_minerai"] > 0) {
					$ressourceMinerai = false;
					foreach($this->echoppeMinerai as $m) {
						if ($m["id_fk_type_echoppe_minerai"] == $r["id_type_minerai"]) {
							if ($r["quantite_lingot_recette_materiel_cout_minerai"] <= $m["quantite_lingots_echoppe_minerai"]) {
								$ressourceMinerai = true;
							} else {
								$ressourceMinerai = false;
							}
							break;
						}
					}
					$ok = "oui";
					if ($ressourceMinerai == false) {
						$ressourcesOk = false;
						$ok = "non";
					}
					$tabCout[] = array(
						"nom" => $r["nom_type_minerai"], 
						"nom_systeme"=> "minerai",
						"id_type_minerai" => $r["id_type_minerai"], 
						"cout" => $r["quantite_lingot_recette_materiel_cout_minerai"], 
						"unite" => "lingot",
						"ressourcesOk" => $ok,
					);
				}
			}

			$recetteCoutPlanteTable = new RecetteMaterielCoutPlante();
			$recetteCoutPlante = $recetteCoutPlanteTable->findByIdTypeMateriel($typeMaterielCourant["id_type_materiel"]);

			$echoppePartieplanteTable = new EchoppePartieplante();
			$partiesPlantes = $echoppePartieplanteTable->findByIdEchoppe($idEchoppe);

			$tabPartiePlantes = null;

			if ($partiesPlantes != null) {
				foreach ($partiesPlantes as $m) {
					if ($m["quantite_preparee_echoppe_partieplante"] >= 1) {
						$tabPartiePlantes[$m["id_fk_type_plante_echoppe_partieplante"]][$m["id_fk_type_echoppe_partieplante"]] = array(
							"nom_type_partieplante" => $m["nom_type_partieplante"],
							"nom_type" => $m["nom_type_plante"],
							"quantite_preparees" => $m["quantite_preparee_echoppe_partieplante"],
						);
					}
				}
			}

			foreach($recetteCoutPlante as $r) {
				$ok = "non";
				$ressourcePlante = false;
				if (isset($tabPartiePlantes[$r["id_fk_type_plante_recette_materiel_cout_plante"]]) && (isset($tabPartiePlantes[$r["id_fk_type_plante_recette_materiel_cout_plante"]][$r["id_fk_type_partieplante_recette_materiel_cout_plante"]]["quantite_preparees"])) ) {
					if ($r["quantite_recette_materiel_cout_plante"] <= $tabPartiePlantes[$r["id_fk_type_plante_recette_materiel_cout_plante"]][$r["id_fk_type_partieplante_recette_materiel_cout_plante"]]["quantite_preparees"]) {
						$ressourcePlante = true;
						$ok = "oui";
					}
				} else {
					$ressourcePlante = false;
				}
					
				if ($ressourcePlante == false) {
					$ressourcesOk = false;
					$ok = "non";
				}
				$tabCout[] = array(
					"nom" => $r["nom_type_plante"], 
					"nom_systeme"=> "plante", 
					"id_type_plante" => $r["id_type_plante"], 
					"id_type_partieplante" => $r["id_type_partieplante"], 
					"unite" => $r["nom_type_partieplante"], 
					"cout" => $r["quantite_recette_materiel_cout_plante"],
					"ressourcesOk" => $ok,
				);
			}

			$this->view->cout = $tabCout;
			$this->view->ressourcesOk = $ressourcesOk;
			$this->view->typeMaterielCourant = $typeMaterielCourant;
		}

		$this->view->typeMateriel = $tabTypeMateriel;
		$this->idEchoppe = $idEchoppe;
		$this->echoppeCourante = $echoppeCourante;
		$this->view->nom_systeme = $this->nom_systeme;
	}

	function prepareFormulaire() {
		if ($this->view->assezDePa == false) {
			return;
		}
	}

	function prepareResultat() {
		// Verification des Pa
		if ($this->view->assezDePa == false) {
			throw new Zend_Exception(get_class($this)." Pas assez de PA : ".$this->view->user->pa_braldun);
		}

		// Verification produire
		if ($this->view->produireEchoppeOk == false) {
			throw new Zend_Exception(get_class($this)." Produire Echoppe interdit ");
		}

		$idTypeMateriel = (int)$this->request->get("valeur_1");

		if ($idTypeMateriel != $this->view->typeMaterielCourant["id_type_materiel"]) {
			throw new Zend_Exception(get_class($this)." idTypeMateriel interdit A=".$idTypeMateriel. " B=".$this->view->typeMaterielCourant["id_type_materiel"]);
		}

		// calcul des jets
		$this->calculJets();

		if ($this->view->okJet1 === true) {
			$this->calculProduire();
		} else {
			$this->calculRateProduire();
		}

		$this->calculPx();
		$this->calculBalanceFaim();
		$this->majBraldun();
	}

	private function calculRateProduire() {
		$this->majCout(false);
	}

	private function calculProduire() {
		$this->majCout(true);

		$dateFin = Bral_Util_ConvertDate::get_date_add_day_to_date(date("Y-m-d H:i:s"), 1);

		Zend_Loader::loadClass("IdsMateriel");
		$idsMaterielTable = new IdsMateriel();
		$idMateriel = $idsMaterielTable->prepareNext();

		Zend_Loader::loadClass("Materiel");
		$materielTable = new Materiel();
		$data = array(
			'id_materiel' => $idMateriel,
			'id_fk_type_materiel' => $this->view->typeMaterielCourant["id_type_materiel"],
		);
		$materielTable->insert($data);

		if (substr($this->view->typeMaterielCourant["nom_systeme_type_materiel"], 0, 9) == "charrette") {
			$data = array(
				"id_charrette" => $idMateriel,
				"durabilite_max_charrette" => $this->view->typeMaterielCourant["durabilite_type_materiel"],
				"durabilite_actuelle_charrette" => $this->view->typeMaterielCourant["durabilite_type_materiel"],
				"poids_transportable_charrette" => $this->view->typeMaterielCourant["capacite_type_materiel"],
				"poids_transporte_charrette" => 0,
			);
			Zend_Loader::loadClass("Charrette");
			$charretteTable = new Charrette();
			$charretteTable->insert($data);
		}

		Zend_Loader::loadClass("EchoppeMateriel");
		$echoppeMaterielTable = new EchoppeMateriel();
		$dataEchoppe = array(
			'id_echoppe_materiel' => $idMateriel,
			'id_fk_echoppe_echoppe_materiel' => $this->idEchoppe,
		);
		$echoppeMaterielTable->insert($dataEchoppe);

		Zend_Loader::loadClass("Bral_Util_Materiel");
		$details = "[b".$this->view->user->id_braldun."] a produit le matériel n°".$idMateriel;
		Bral_Util_Materiel::insertHistorique(Bral_Util_Materiel::HISTORIQUE_CREATION_ID, $idMateriel, $details);

		$this->view->id_materiel_cree = $idMateriel;
	}

	private function majCout($estReussi) {

		if ($estReussi) {
			$coef = 1;
		} else {
			$coef = 2;
		}

		$echoppeMineraiTable = new EchoppeMinerai();

		foreach($this->view->cout as $c) {
			switch ($c["nom_systeme"]) {
				case "cuir" :
					$this->echoppeCourante["quantite_cuir_arriere_echoppe"] = $this->echoppeCourante["quantite_cuir_arriere_echoppe"] - floor($c["cout"] / $coef);
					if ($this->echoppeCourante["quantite_cuir_arriere_echoppe"] < 0) {
						$this->echoppeCourante["quantite_cuir_arriere_echoppe"] = 0;
					}
					break;
				case "fourrure" :
					$this->echoppeCourante["quantite_fourrure_arriere_echoppe"] = $this->echoppeCourante["quantite_fourrure_arriere_echoppe"] - floor($c["cout"] / $coef);
					if ($this->echoppeCourante["quantite_fourrure_arriere_echoppe"] < 0) {
						$this->echoppeCourante["quantite_fourrure_arriere_echoppe"] = 0;
					}
					break;
				case "planche" :
					$this->echoppeCourante["quantite_planche_arriere_echoppe"] = $this->echoppeCourante["quantite_planche_arriere_echoppe"] - floor($c["cout"] / $coef);
					if ($this->echoppeCourante["quantite_planche_arriere_echoppe"] < 0) {
						$this->echoppeCourante["quantite_planche_arriere_echoppe"] = 0;
					}
					break;
				case "minerai" :
					if (!isset($c["id_type_minerai"])) {
						throw new Zend_Exception(get_class($this)." Minerai inconnu ".$c["nom_systeme"]);
					}
					foreach($this->echoppeMinerai as $m) {
						if ($m["id_fk_type_echoppe_minerai"] == $c["id_type_minerai"]) {
							$quantite = $m["quantite_lingots_echoppe_minerai"] - floor($c["cout"] / $coef);
							if ($quantite < 0) {
								$quantite = 0;
							}
							$data = array('quantite_lingots_echoppe_minerai' => $quantite);
							$where = 'id_fk_type_echoppe_minerai = '. $c["id_type_minerai"];
							$where .= ' AND id_fk_echoppe_echoppe_minerai='.$this->echoppeCourante["id_echoppe"];
							$echoppeMineraiTable->update($data, $where);
						}
					}
					break;
				case "plante" :
					$echoppePartiePlanteTable = new EchoppePartieplante();
					$data = array('quantite_preparee_echoppe_partieplante' => - floor($c["cout"] / $coef),
						  'id_fk_type_echoppe_partieplante' => $c["id_type_partieplante"],
						  'id_fk_type_plante_echoppe_partieplante' => $c["id_type_plante"],
						  'id_fk_echoppe_echoppe_partieplante' => $this->echoppeCourante["id_echoppe"]);
					$echoppePartiePlanteTable->insertOrUpdate($data);
					break;
				default :
					throw new Zend_Exception(get_class($this)." Type inconnu ".$c["nom_systeme"]);
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
	}

	public function getIdEchoppeCourante() {
		if (isset($this->idEchoppe)) {
			return $this->idEchoppe;
		} else {
			return false;
		}
	}

	function getListBoxRefresh() {
		return $this->constructListBoxRefresh(array("box_competences", "box_echoppes"));
	}
}
