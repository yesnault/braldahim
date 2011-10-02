<?php
/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Competences_Pister extends Bral_Competences_Competence
{
	function prepareCommun()
	{
		Zend_Loader::loadClass("Bral_Util_Commun");
		Zend_Loader::loadClass("BraldunsCdm");
		//Zend_Loader::loadClass("TypeMonstre");

		// Position précise avec (Vue+BM) de vue *2
		$this->view->rayon_precis = (Bral_Util_Commun::getVueBase($this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun) + $this->view->user->vue_bm_braldun) * 2;

		$typeMonstreTable = new BraldunsCdm();
		$typeMonstreBraldun = $typeMonstreTable->findByIdBraldun($this->view->user->id_braldun);
		$tabTypeMonstrePistable = null;
		$tabTypeMonstreCdm = null;
		$braldunsCdmTable = new BraldunsCdm();
		foreach ($typeMonstreBraldun as $t) {
			$typeMonstreCdm = null;
			$typeMonstreCdm = $braldunsCdmTable->findByIdBraldunAndIdTypeMonstre($this->view->user->id_braldun, $t["id_fk_type_monstre_hcdm"]);
			if (count($typeMonstreCdm) == 0) {
				$tabTypeMonstrePistable[] = array(
					'id_type_monstre' => $t["id_fk_type_monstre_hcdm"],
					'nom_type_monstre' => $t["nom_type_monstre"],
				);
			}
			else {
				$tabTypeMonstreCdm[] = array(
					'id_type_monstre' => $t["id_fk_type_monstre_hcdm"],
					'nom_type_monstre' => $t["nom_type_monstre"],
					'tailles' => $typeMonstreCdm
				);
			}
		}
		$this->view->tabTailleMonstre = $braldunsCdmTable->findTaille();
		$this->view->tabTypeMonstrePistable = $tabTypeMonstrePistable;
		$this->view->tabTypeMonstreCdm = $tabTypeMonstreCdm;
	}

	function prepareFormulaire()
	{

	}

	function prepareResultat()
	{

		// Verification des Pa
		if ($this->view->assezDePa == false) {
			throw new Zend_Exception(get_class($this) . " Pas assez de PA : " . $this->view->user->pa_braldun);
		}

		if (((int)$this->request->get("valeur_1") . "" != $this->request->get("valeur_1") . "")) {
			throw new Zend_Exception(get_class($this) . " Type de monstre invalide : " . $this->request->get("valeur_1"));
		} else {
			$idTypeMonstre = (int)$this->request->get("valeur_1");
		}

		$pister = false;
		if (isset($this->view->tabTypeMonstrePistable) && count($this->view->tabTypeMonstrePistable) > 0) {
			foreach ($this->view->tabTypeMonstrePistable as $m) {
				if ($m["id_type_monstre"] == $idTypeMonstre) {
					$pister = true;
					$this->view->nomTypeMonstre = $m["nom_type_monstre"];
					break;
				}
			}
		}
		if ($pister === false) {
			throw new Zend_Exception(get_class($this) . " Type de monstre invalide (" . $idTypeMonstre . ")");
		}

		$this->calculJets();
		if ($this->view->okJet1 === true) {
			$this->calculPister($idTypeMonstre);
		}

		$this->calculPx();
		$this->calculBalanceFaim();
		$this->majBraldun();
	}

	private function calculPister($idTypeMonstre)
	{
		Zend_Loader::loadClass("Monstre");
		// La distance max de repérage d'un monstre est : jet SAG+BM
		$tirageRayonMax = Bral_Util_De::getLanceDe6($this->view->config->game->base_sagesse + $this->view->user->sagesse_base_braldun);
		$this->view->rayon_max = $tirageRayonMax + $this->view->user->sagesse_bm_braldun + $this->view->user->sagesse_bbdf_braldun;

		$monstreTable = new Monstre();
		$monstreRow = $monstreTable->findLePlusProcheParType($idTypeMonstre, $this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun, $this->view->rayon_max);

		if (!empty($monstreRow)) {
			$monstre = array(
				'nom_type_monstre' => $monstreRow["nom_type_monstre"],
				'x_monstre' => $monstreRow["x_monstre"],
				'y_monstre' => $monstreRow["y_monstre"]);
			$this->view->trouve = true;
			$this->view->monstre = $monstre;
			if ($monstreRow["distance"] <= $this->view->rayon_precis) {
				$this->view->proche = true;
			} else {
				$this->view->proche = false;
			}
		} else {
			$this->view->trouve = false;
		}

	}

	function getListBoxRefresh()
	{
		return $this->constructListBoxRefresh(array("box_competences"));
	}
}