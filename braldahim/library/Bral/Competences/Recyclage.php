<?php
/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */

class Bral_Competences_Recyclage extends Bral_Competences_Competence {

	function prepareCommun() {
		Zend_Loader::loadClass("LabanEquipement");

		/*
		 * Si le Braldûn n'a pas de PA, on ne fait aucun traitement
		 */
		$this->calculNbPa();
		if ($this->view->assezDePa == false) {
			return;
		}

		// on va chercher l'équipement présent dans le laban
		$tabEquipementLaban = null;
		$labanEquipementTable = new LabanEquipement();
		$equipementLabanRowset = $labanEquipementTable->findByIdBraldun($this->view->user->id_braldun);

		Zend_Loader::loadClass("Bral_Util_Equipement");

		foreach ($equipementLabanRowset as $e) {
			$tabEquipementLaban[] = array(
				"id_equipement" => $e["id_laban_equipement"],
				"nom" => Bral_Util_Equipement::getNomByIdRegion($e, $e["id_fk_region_equipement"]),
				"qualite" => $e["nom_type_qualite"],
				"niveau" => $e["niveau_recette_equipement"],
				"id_type" => $e["id_type_equipement"],
				"poids" => $e["poids_equipement"],
				"suffixe" => $e["suffixe_mot_runique"],
			);
		}
		$this->view->tabEquipementLaban = $tabEquipementLaban;
		$this->view->nbEquipementLaban = count($tabEquipementLaban);

	}

	function prepareFormulaire() {
		if ($this->view->assezDePa == false) {
			return;
		}
	}

	function prepareResultat() {

		// Verification des Pa
		if ($this->view->assezDePa == false) {
			throw new Zend_Exception(get_class($this) . " Pas assez de PA : " . $this->view->user->pa_braldun);
		}

		if (((int)$this->request->get("valeur_1") . "" != $this->request->get("valeur_1") . "")) {
			throw new Zend_Exception(get_class($this) . " Equipement invalide : " . $this->request->get("valeur_1"));
		} else {
			$idEquipement = (int)$this->request->get("valeur_1");
		}

		$recyclage = false;
		if (isset($this->view->tabEquipementLaban) && $this->view->nbEquipementLaban > 0) {
			foreach ($this->view->tabEquipementLaban as $e) {
				if ($e["id_equipement"] == $idEquipement) {
					$idTypeEquipement = $e["id_type"];
					$nivEquipement = $e["niveau"];
					$poidsEquipement = $e["poids"];
					$recyclage = true;
					break;
				}
			}
		}
		if ($recyclage === false) {
			throw new Zend_Exception(get_class($this) . " Equipement invalide (" . $idEquipement . ")");
		}

		$this->calculJets();
		if ($this->view->okJet1 === true) {
			$this->calculRecyclage($idEquipement, $idTypeEquipement, $nivEquipement, $poidsEquipement);
		}
		$this->calculPx();
		$this->calculBalanceFaim();
		$this->calculPoids();
		$this->majBraldun();
	}

	private function calculRecyclage($idEquipement, $idTypeEquipement, $nivEquipement, $poidsEquipement) {
		Zend_Loader::loadClass("RecetteCout");
		Zend_Loader::loadClass("RecetteCoutMinerai");
		Zend_Loader::loadClass("Laban");
		Zend_Loader::loadClass("Element");

		$nbCuir = 0;
		$nbFourrure = 0;
		$nbPlanche = 0;
		$tabMineraiLaban = null;
		$tabMineraiTerre = null;

		$this->poidsRestant = $this->view->user->poids_transportable_braldun - $this->view->user->poids_transporte_braldun + $poidsEquipement;

		$labanEquipementTable = new LabanEquipement();
		$where = "id_laban_equipement=" . $idEquipement;
		$labanEquipementTable->delete($where);

		Zend_Loader::loadClass("Bral_Util_Equipement");
		Bral_Util_Equipement::destructionEquipement($idEquipement);

		$recetteCoutTable = new RecetteCout();
		$recetteCout = $recetteCoutTable->findByIdTypeEquipementAndNiveau($idTypeEquipement, $nivEquipement);

		/*
		 * Soit Ni le niveau de la pièce d'équipement. Soit Js un jet de SAG + BM.
		 * Si Js < Ni*20 alors 20% de chacun des composants arrondis à l'inférieur sont récupérés
		 * Si Ni*20 <= Js < Ni*30 alors 40 % des ...
		 * Si Ni*30 <= Js < Ni*40 alors 50 % des ...
		 * Si Ni*40 <= Js alors 50 % des ...
		 */

		$jetSag = Bral_Util_De::getLanceDe6($this->view->config->game->base_sagesse + $this->view->user->sagesse_base_braldun);
		$jetSag = $jetSag + $this->view->user->sagesse_bm_braldun + $this->view->user->sagesse_bbdf_braldun;

		$perte = 0.20; // pour le niveau 0

		if ($nivEquipement == 0) {
			$perte = 0.20; // pour le niveau 0
		} elseif ($jetSag < $nivEquipement * 20) {
			$perte = 0.20;
		} elseif ($jetSag >= $nivEquipement * 20 && $jetSag < $nivEquipement * 30) {
			$perte = 0.4;
		} elseif ($jetSag >= $nivEquipement * 30 && $jetSag < $nivEquipement * 40) {
			$perte = 0.5;
		} elseif ($jetSag >= $nivEquipement * 40) {
			$perte = 0.5;
		}

		foreach ($recetteCout as $r) {
			$nbCuir = floor($r["cuir_recette_cout"] * $perte);
			$nbFourrure = floor($r["fourrure_recette_cout"] * $perte);
			$nbPlanche = floor($r["planche_recette_cout"] * $perte);
		}

		$nbCuirLaban = $this->calculNbPoidsPossible($nbCuir, Bral_Util_Poids::POIDS_CUIR);
		$nbCuirTerre = $nbCuir - $nbCuirLaban;

		$nbFourrureLaban = $this->calculNbPoidsPossible($nbFourrure, Bral_Util_Poids::POIDS_FOURRURE);
		$nbFourrureTerre = $nbFourrure - $nbFourrureLaban;

		$nbPlancheLaban = $this->calculNbPoidsPossible($nbPlanche, Bral_Util_Poids::POIDS_PLANCHE);
		$nbPlancheTerre = $nbPlanche - $nbPlancheLaban;

		// on ajoute dans le laban
		$labanTable = new Laban();
		$data = array(
			'id_fk_braldun_laban' => $this->view->user->id_braldun,
			'quantite_cuir_laban' => $nbCuirLaban,
			'quantite_fourrure_laban' => $nbFourrureLaban,
			'quantite_planche_laban' => $nbPlancheLaban,
		);
		$labanTable->insertOrUpdate($data);

		// on depose le trop plein à terre
		$elementTable = new Element();
		$data = array(
			"quantite_cuir_element" => $nbCuirTerre,
			"quantite_fourrure_element" => $nbFourrureTerre,
			"quantite_planche_element" => $nbPlancheTerre,
			"x_element" => $this->view->user->x_braldun,
			"y_element" => $this->view->user->y_braldun,
			"z_element" => $this->view->user->z_braldun,
		);
		$elementTable->insertOrUpdate($data);

		$recetteCoutMineraiTable = new RecetteCoutMinerai();
		$recetteCoutMinerai = $recetteCoutMineraiTable->findByIdTypeEquipementAndNiveau($idTypeEquipement, $nivEquipement);
		if (count($recetteCoutMinerai) > 0) {
			Zend_Loader::loadClass("LabanMinerai");
			Zend_Loader::loadClass("ElementMinerai");

			$labanMineraiTable = new LabanMinerai();
			foreach ($recetteCoutMinerai as $r) {
				$nbMinerai = floor($r["quantite_recette_cout_minerai"] * $perte);

				$nbMineraiLaban = $this->calculNbPoidsPossible($nbMinerai, Bral_Util_Poids::POIDS_LINGOT);
				$nbMineraiTerre = $nbMinerai - $nbMineraiLaban;

				if ($nbMineraiLaban > 0) {
					// on ajoute dans le laban
					$tabMineraiLaban[] = array(
						"nom" => $r["nom_type_minerai"],
						"quantite" => $nbMineraiLaban,
					);

					$data = array(
						'id_fk_type_laban_minerai' => $r["id_type_minerai"],
						'id_fk_braldun_laban_minerai' => $this->view->user->id_braldun,
						'quantite_lingots_laban_minerai' => $nbMineraiLaban,
					);
					$labanMineraiTable->insertOrUpdate($data);
				}

				if ($nbMineraiTerre > 0) {
					// on depose le trop plein à terre
					$tabMineraiTerre[] = array(
						"nom" => $r["nom_type_minerai"],
						"quantite" => $nbMineraiTerre,
					);

					$elementMineraiTable = new ElementMinerai();
					$data = array(
						"x_element_minerai" => $this->view->user->x_braldun,
						"y_element_minerai" => $this->view->user->y_braldun,
						"z_element_minerai" => $this->view->user->z_braldun,
						"id_fk_type_element_minerai" => $r["id_type_minerai"],
						"quantite_lingots_element_minerai" => $nbMineraiTerre,
					);
					$elementMineraiTable->insertOrUpdate($data);
				}
			}
		}

		$this->view->nbCuirLaban = $nbCuirLaban;
		$this->view->nbFourrureLaban = $nbFourrureLaban;
		$this->view->nbPlancheLaban = $nbPlancheLaban;
		$this->view->mineraiLaban = $tabMineraiLaban;
		$this->view->nbCuirTerre = $nbCuirTerre;
		$this->view->nbFourrureTerre = $nbFourrureTerre;
		$this->view->nbPlancheTerre = $nbPlancheTerre;
		$this->view->mineraiTerre = $tabMineraiTerre;
		$this->view->jetRecyclage = $jetSag;

		$details = "[b" . $this->view->user->id_braldun . "] a recyclé la pièce d'équipement n°" . $idEquipement;
		Bral_Util_Equipement::insertHistorique(Bral_Util_Equipement::HISTORIQUE_DESTRUCTION_ID, $idEquipement, $details);

	}

	private function calculNbPoidsPossible($quantite, $poidsType) {
		if ($quantite < 0) $quantite = 0;
		if ($this->poidsRestant < 0) $this->poidsRestant = 0;
		$quantitePossible = floor($this->poidsRestant / $poidsType);
		if ($quantite > $quantitePossible) $quantite = $quantitePossible;
		$this->poidsRestant = $this->poidsRestant - ($poidsType * $quantite);
		return $quantite;
	}

	function getListBoxRefresh() {
		return $this->constructListBoxRefresh(array("box_competences", "box_laban", "box_vue"));
	}
}
