<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: $
 * $Author: $
 * $LastChangedDate: $
 * $LastChangedRevision: $
 * $LastChangedBy: $
 */
abstract class Bral_Competences_Produire extends Bral_Competences_Competence {

	function prepareCommun() {
		Zend_Loader::loadClass("Echoppe");
		Zend_Loader::loadClass("TypeMateriel");
		Zend_Loader::loadClass("Bral_Helper_DetailMateriel");

		$id_type_courant = $this->request->get("type_materiel");

		$typeMaterielCourant = null;

		// On regarde si le hobbit est dans une de ses echopppes
		$echoppeTable = new Echoppe();
		$echoppes = $echoppeTable->findByCase($this->view->user->x_hobbit, $this->view->user->y_hobbit);

		$this->view->produireEchoppeOk = false;
		if ($echoppes == null || count($echoppes) == 0) {
			$this->view->produireEchoppeOk = false;
			return;
		}

		$idEchoppe = -1;
		$metier = substr($this->nom_systeme, 8, strlen($this->nom_systeme) - 8);
		foreach($echoppes as $e) {
			if ($e["id_fk_hobbit_echoppe"] == $this->view->user->id_hobbit &&
			$e["nom_systeme_metier"] == $metier &&
			$e["x_echoppe"] == $this->view->user->x_hobbit &&
			$e["y_echoppe"] == $this->view->user->y_hobbit) {
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
				'capacite' => $t["capacite_base_type_materiel"], 
				'durabilite' => $t["durabilite_base_type_materiel"], 
				'usure' => $t["usure_base_type_materiel"], 
				'poids' => 'TODO',
				'selected' => $selected
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
			Zend_Loader::loadClass("EchoppeMinerai");

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
						"id_type_minerai"=>$r["id_type_minerai"], 
						"cout" => $r["quantite_lingot_recette_materiel_cout_minerai"], 
						"unite" => "lingot",
						"ressourcesOk" => $ok,
					);
				}
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
			throw new Zend_Exception(get_class($this)." Pas assez de PA : ".$this->view->user->pa_hobbit);
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
		$this->majHobbit();
	}

	private function calculRateProduire() {
		$this->majCout(false);
	}

	private function calculProduire() {
		$this->majCout(true);

		$dateFin = Bral_Util_ConvertDate::get_date_add_day_to_date(date("Y-m-d H:i:s"), 1);

		Zend_Loader::loadClass("ElementMateriel");
		$elementMaterielTable = new ElementMateriel();
		$data = array(
			'id_fk_type_element_materiel' => $this->view->typeMaterielCourant["id_type_materiel"],
			'date_fin_element_materiel' => 'aucune',
			'x_element' => $this->view->user->x_hobbit,
			'y_element' => $this->view->user->y_hobbit,
			'date_fin_element_materiel' => $dateFin,
		);
		$idMateriel = $elementMaterielTable->insert($data);

		Zend_Loader::loadClass("EchoppeMateriel");
		$echoppeMaterielTable = new EchoppeMateriel();
		$data = array(
			'id_echoppe_materiel' => $idMateriel,
			'id_fk_echoppe_echoppe_materiel' => $this->idEchoppe,
			'id_fk_type_echoppe_materiel' => $this->view->typeMaterielCourant["id_type_materiel"],
			'type_vente_echoppe_materiel' => 'aucune',
		);
		$echoppeMaterielTable->insert($data);

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
				case "plantes" :
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
		return $this->constructListBoxRefresh(array("box_competences_metiers", "box_echoppes"));
	}
}
