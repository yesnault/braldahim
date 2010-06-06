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
class Bral_Competences_Reanimer extends Bral_Competences_Competence {

	function prepareCommun() {

		$tabBralduns = null;

		if ($this->view->user->est_intangible_braldun == 'oui') {
			return;
		}

		// recuperation des bralduns qui sont presents sur la case
		$braldunTable = new Braldun();
		$bralduns = $braldunTable->findByCase($this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun, $this->view->user->id_braldun, false, true);

		$tabBralduns = null;

		if (count($bralduns) > 0) {
			foreach($bralduns as $b) {
				if ($b["niveau_braldun"] < ($this->view->user->sagesse_base_braldun * 2)) {
					$tabBralduns[] = $b;
				}
			}
		}


		Zend_Loader::loadClass("LabanAliment");
		Zend_Loader::loadClass("Bral_Util_Aliment");

		$labanAlimentTable = new LabanAliment();
		$aliments = $labanAlimentTable->findByIdBraldun($this->view->user->id_braldun);

		$tabAliments = null;
		$tabBoissons = null;
		foreach ($aliments as $p) {
			$tab = array(
					"id_aliment" => $p["id_aliment"],
					"id_fk_type_aliment" => $p["id_fk_type_aliment"],
					"id_fk_type_qualite_aliment" => $p["id_fk_type_qualite_aliment"],
					"nom" => $p["nom_type_aliment"],
					"recette" => Bral_Util_Aliment::getNomType($p["type_bbdf_type_aliment"]),
					"qualite" => $p["nom_type_qualite"],
					"bbdf" => $p["bbdf_aliment"],
					"id_fk_effet_braldun_aliment" => $p["id_fk_effet_braldun_aliment"],
			);

			if ($p["type_type_aliment"] == "manger") {
				$tabAliments[$p["id_aliment"]] = $tab;
			} else {
				$tabBoissons[$p["id_aliment"]] = $tab;
			}
		}


		$this->view->tabBralduns = $tabBralduns;
		$this->view->nBralduns = count($tabBralduns);

		$this->view->tabAliments = count($tabAliments);
		$this->view->nAliments = count($tabAliments);

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
			throw new Zend_Exception(get_class($this)." Braldun invalide : ".$this->request->get("valeur_1"));
		} else {
			$idBraldun = (int)$this->request->get("valeur_1");
		}

		if ($idBraldun == -1) {
			throw new Zend_Exception(get_class($this)." Caract ou Braldun invalide");
		}

		$choixBraldun = false;

		if (isset($this->view->tabBralduns) && count($this->view->tabBralduns) > 0) {
			foreach ($this->view->tabBralduns as $h) {
				if ($h["id_braldun"] == $idBraldun) {
					$choixBraldun = true;
					$braldun = $h;
					break;
				}
			}
		}
		if ($choixBraldun === false) {
			throw new Zend_Exception(get_class($this)." Braldun invalide (".$idBraldun.")");
		}


		$this->reanimer($braldun);
		$this->setEvenementQueSurOkJet1(false);
		$this->calculPx();
		$this->calculBalanceFaim();
		$this->majBraldun();

		$this->view->caract = $caract;
		$this->view->braldun = $braldun;
	}

	function reanimer($braldun) {

		//TODO


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