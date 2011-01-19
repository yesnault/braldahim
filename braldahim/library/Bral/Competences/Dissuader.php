<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Competences_Dissuader extends Bral_Competences_Competence {

	function prepareCommun() {
		Zend_Loader::loadClass("Monstre");
		Zend_Loader::loadClass("Bral_Util_Attaque");

		$tabBralduns = null;
		$tabMonstres = null;

		$estRegionPvp = Bral_Util_Attaque::estRegionPvp($this->view->user->x_braldun, $this->view->user->y_braldun);

		$xMin = $this->view->user->x_braldun - 1;
		$xMax = $this->view->user->x_braldun + 1;
		$yMin = $this->view->user->y_braldun - 1;
		$yMax = $this->view->user->y_braldun + 1;

		$dissuaderPossible = false;
		
		if ($this->view->user->est_soule_braldun == "oui") {
			return;	
		}

		if ($estRegionPvp) {
			// recuperation des bralduns qui sont presents dans un rayon de 1
			$braldunTable = new Braldun();
			$bralduns = $braldunTable->selectVue($xMin, $yMin, $xMax, $yMax, $this->view->user->z_braldun, $this->view->user->id_braldun, false);
			if (count($bralduns) > 0) {
				$dissuaderPossible = true;
			}
		}

		// recuperation des monstres qui sont presents dans un rayon de 1
		$monstreTable = new Monstre();
		$monstres = $monstreTable->selectVue($xMin, $yMin, $xMax, $yMax, $this->view->user->z_braldun);
		if (count($monstres) > 0) {
			$dissuaderPossible = true;
		}

		$this->bralduns = $bralduns;
		$this->monstres = $monstres;

		$this->view->dissuaderPossible = $dissuaderPossible;
		$this->view->estRegionPvp = $estRegionPvp;
	}

	function prepareFormulaire() {
		// rien à faire ici
	}

	function prepareResultat() {

		// Verification des Pa
		if ($this->view->assezDePa == false) {
			throw new Zend_Exception(get_class($this)." Pas assez de PA : ".$this->view->user->pa_braldun);
		}

		if ($this->view->dissuaderPossible == false) {
			throw new Zend_Exception(get_class($this)." Dissuader impossible");
		}

		$this->dissuader();
		$this->setEvenementQueSurOkJet1(false);
		$this->calculPx();
		$this->calculBalanceFaim();
		$this->majBraldun();
	}

	function dissuader() {
		$jetBraldun = Bral_Util_De::getLanceDe6($this->view->config->game->base_sagesse + $this->view->user->sagesse_base_braldun);
		$this->view->jetBraldun = $jetBraldun + $this->view->user->sagesse_bm_braldun + $this->view->user->sagesse_bbdf_braldun;

		$sommeJets = 0;

		if (count($this->bralduns) > 0) {
			foreach($this->bralduns as $b) {
				$sommeJets = $sommeJets + Bral_Util_De::getLanceDe6($this->view->config->game->base_sagesse + $b["sagesse_base_braldun"]);
				$sommeJets = $sommeJets + $b["sagesse_bm_braldun"] + $b["sagesse_bbdf_braldun"];
			}
		}

		if (count($this->monstres) > 0) {
			foreach($this->monstres as $m) {
				$sommeJets = $sommeJets + Bral_Util_De::getLanceDe6($this->view->config->game->base_sagesse + $m["sagesse_base_monstre"]);
				$sommeJets = $sommeJets + $m["sagesse_bm_monstre"];
			}
		}
		
		$dissuaderOk = false;
		if ($this->view->jetBraldun > $sommeJets) {
			$data = array(
				'est_intangible_braldun' => 'oui'
			);
			$tableBraldun = new Braldun();
			$where = "id_braldun = ".$this->view->user->id_braldun;
			$tableBraldun->update($data, $where);
			$dissuaderOk = true;
			$this->view->okJet1 = true;
		}
		
		$this->view->dissuaderOk = $dissuaderOk;
		$this->view->sommeJets = $sommeJets;
	}

	function getListBoxRefresh() {
		return $this->constructListBoxRefresh();
	}

}