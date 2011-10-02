<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Competences_Sonder extends Bral_Competences_Competence
{

	function prepareCommun()
	{
		Zend_Loader::loadClass('Bral_Util_Commun');

		// Position précise avec (Vue+BM) de vue *2
		$this->view->rayon_precis = (Bral_Util_Commun::getVueBase($this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun) + $this->view->user->vue_bm_braldun) * 2;

		$this->view->avecChoix = false;
		$tabChoix = array();

		if ($this->braldun_competence["pourcentage_hcomp"] >= 80) {
			$this->view->avecChoix = true;

			Zend_Loader::loadClass("TypeMinerai");
			$typeMineraiTable = new TypeMinerai();
			$tabChoix = $typeMineraiTable->fetchAll();
		}
		$this->view->tabChoix = $tabChoix;
	}

	function prepareFormulaire()
	{
		if ($this->view->assezDePa == false) {
			return;
		}
	}

	function prepareResultat()
	{

		if (((int)$this->request->get("valeur_1") . "" != $this->request->get("valeur_1") . "")) {
			throw new Zend_Exception(get_class($this) . " Valeur invalide : " . $this->request->get("valeur_1"));
		} else {
			$choix = (int)$this->request->get("valeur_1");
		}

		if ($choix > count($this->view->tabChoix) - 1 || $choix == -1) {
			throw new Zend_Exception(get_class($this) . " Valeur invalide  2 : " . $choix);
		}

		$this->calculJets();

		if ($this->view->okJet1 === true) {
			$this->sonder($choix);
		}

		$this->calculPx();
		$this->calculBalanceFaim();
		$this->majBraldun();
	}

	private function sonder($choix)
	{
		// La distance max de repérage d'un filon est : jet VIG+BM
		$tirageRayonMax = Bral_Util_De::getLanceDe6($this->view->config->game->base_vigueur + $this->view->user->vigueur_base_braldun);
		$this->view->rayon_max = $tirageRayonMax + $this->view->user->vigueur_bm_braldun + $this->view->user->vigueur_bbdf_braldun;

		$idTypeMinerai = null;
		$this->view->libelleRecherche = "le filon le plus proche";

		if ($choix != -3 && $choix != -2) {
			$idTypeMinerai = $this->view->tabChoix[$choix]["id_type_minerai"];

			$this->view->libelleRecherche = "un filon de type " . $this->view->tabChoix[$choix]["nom_type_minerai"];
		}

		Zend_Loader::loadClass('Filon');
		$filonTable = new Filon();
		$filonRow = $filonTable->findLePlusProche($this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun, $this->view->rayon_max, $idTypeMinerai);
		unset($filonTable);

		if (!empty($filonRow)) {
			$f = array(
				'type_minerai' => $filonRow["nom_type_minerai"],
				'x_filon' => $filonRow["x_filon"],
				'y_filon' => $filonRow["y_filon"],
				'z_filon' => $filonRow["z_filon"],
			);
			$this->view->trouve = true;
			$this->view->filon = $f;
			if ($filonRow["distance"] <= $this->view->rayon_precis) {
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