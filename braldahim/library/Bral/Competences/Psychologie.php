<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Competences_Psychologie extends Bral_Competences_Competence {

	function prepareCommun() {
		Zend_Loader::loadClass("Monstre");
		Zend_Loader::loadClass("Bral_Monstres_VieMonstre");
		Zend_Loader::loadClass('Bral_Util_Commun');
		Zend_Loader::loadClass('Bral_Util_Attaque');
		Zend_Loader::loadClass("BraldunEquipement");

		$tabBralduns = null;

		if ($this->view->user->est_intangible_braldun == 'oui') {
			return;
		}

		// recuperation des bralduns qui sont presents sur la case
		$braldunTable = new Braldun();
		$bralduns = $braldunTable->findByCase($this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun, $this->view->user->id_braldun, false);

		$tabCaracs['force'] = array('nom' => 'Force');
		$tabCaracs['agilite'] = array('nom' => 'Agilité');
		$tabCaracs['vigueur'] = array('nom' => 'Vigueur');
		$tabCaracs['sagesse'] = array('nom' => 'Sagesse');

		$this->view->tabCaracs = $tabCaracs;
		$this->view->tabBralduns = $bralduns;
		$this->view->nBralduns = count($bralduns);
	}

	function prepareFormulaire() {
		// rien à faire ici
	}

	function prepareResultat() {

		// Verification des Pa
		if ($this->view->assezDePa == false) {
			throw new Zend_Exception(get_class($this)." Pas assez de PA : ".$this->view->user->pa_braldun);
		}
		if (((int)$this->request->get("valeur_1").""!=$this->request->get("valeur_1")."")) {
			throw new Zend_Exception(get_class($this)." Braldûn invalide : ".$this->request->get("valeur_1"));
		} else {
			$idBraldun = (int)$this->request->get("valeur_1");
		}

		$caract = null;
		if (!array_key_exists($this->request->get("valeur_2"), $this->view->tabCaracs)) {
			throw new Zend_Exception(get_class($this)." Caract invalide : ".$this->request->get("valeur_2"));
		} else {
			$caract = $this->request->get("valeur_2");
		}

		if ($idBraldun == -1 || $caract == null) {
			throw new Zend_Exception(get_class($this)." Caract ou Braldûn invalide");
		}

		$psychologieBraldun = false;

		if (isset($this->view->tabBralduns) && count($this->view->tabBralduns) > 0) {
			foreach ($this->view->tabBralduns as $h) {
				if ($h["id_braldun"] == $idBraldun) {
					$psychologieBraldun = true;
					$braldun = $h;
					break;
				}
			}
		}
		if ($psychologieBraldun === false) {
			throw new Zend_Exception(get_class($this)." Braldûn invalide (".$idBraldun.")");
		}


		$this->psychologie($braldun, $caract);
		$this->setEvenementQueSurOkJet1(false);
		$this->calculPx();
		$this->calculBalanceFaim();
		$this->majBraldun();

		$this->view->caract = $caract;
		$this->view->braldun = $braldun;
	}

	function psychologie($braldun, $caracteristique) {
		$jetBraldun = Bral_Util_De::getLanceDe6($this->view->config->game->base_sagesse + $this->view->user->sagesse_base_braldun);
		$this->view->jetBraldun = $jetBraldun + $this->view->user->sagesse_bm_braldun + $this->view->user->sagesse_bbdf_braldun;


		$jetCible = Bral_Util_De::getLanceDe6($this->view->config->game->base_sagesse + $braldun[$caracteristique."_base_braldun"]);
		$this->view->jetCible = $jetCible + $braldun[$caracteristique."_bm_braldun"] + $braldun[$caracteristique."_bbdf_braldun"];

		$psychologieOk = false;

		if ($this->view->jetBraldun > $this->view->jetCible) {
			$psychologieOk = true;
			$maj = false;

			if ($braldun[$caracteristique."_bbdf_braldun"] < 0) {
				$maj = true;
				$data[$caracteristique."_bbdf_braldun"] = 0;
			}

			if ($braldun[$caracteristique."_bm_braldun"] < 0) {
				$maj = true;
				$data[$caracteristique."_bm_braldun"] = 0;
			}

			if ($maj) {
				$braldunTable = new Braldun();
				$where = "id_braldun = ".$braldun["id_braldun"];
				$braldunTable->update($data, $where);
			}
		}

		$this->view->psychologieOk = $psychologieOk;
	}

	function getListBoxRefresh() {
		return $this->constructListBoxRefresh();
	}

}