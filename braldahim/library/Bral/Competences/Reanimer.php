<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
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
			foreach ($bralduns as $b) {
				if ($b["niveau_braldun"] < ($this->view->user->sagesse_base_braldun * 2)) {
					$tabBralduns[] = $b;
				}
			}
		}


		Zend_Loader::loadClass("Bral_Util_Aliment");
		$tabAliments = null;
		$tabAlimentsType = null;

		Zend_Loader::loadClass("LabanAliment");
		$labanAlimentTable = new LabanAliment();
		$aliments = $labanAlimentTable->findByIdBraldun($this->view->user->id_braldun);
		$this->prepareAliment($tabAliments, $aliments, "laban");

		Zend_Loader::loadClass("Charrette");
		$charretteTable = new Charrette();
		$charrette = $charretteTable->findByIdBraldun($this->view->user->id_braldun);

		if ($charrette != null && count($charrette) == 1) {
			Zend_Loader::loadClass("CharretteAliment");
			$charretteAlimentTable = new CharretteAliment();
			$aliments = $charretteAlimentTable->findByIdCharrette($charrette[0]["id_charrette"]);
			$this->prepareAliment($tabAliments, $aliments, "charrette");
		}

		$this->view->tabBralduns = $tabBralduns;
		$this->view->nBralduns = count($tabBralduns);

		$this->view->tabAliments = $tabAliments;
		$this->view->nAliments = count($tabAliments);

	}

	private function prepareAliment(&$tabAliments, $aliments, $type) {
		foreach ($aliments as $p) {
			if ($p["type_type_aliment"] == "manger") {
				$tab = array(
					"id_aliment" => $p["id_aliment"],
					"id_fk_type_aliment" => $p["id_fk_type_aliment"],
					"id_fk_type_qualite_aliment" => $p["id_fk_type_qualite_aliment"],
					"nom" => $p["nom_type_aliment"],
					"recette" => Bral_Util_Aliment::getNomType($p["type_bbdf_type_aliment"]),
					"qualite" => $p["nom_type_qualite"],
					"bbdf" => $p["bbdf_aliment"],
					"id_fk_effet_braldun_aliment" => $p["id_fk_effet_braldun_aliment"],
					"type" => $type,
				);
				$tabAliments[$p["id_aliment"]] = $tab;
			}
		}
	}

	function prepareFormulaire() {
		// rien à faire ici
	}

	function prepareResultat() {

		// Verification des Pa
		if ($this->view->assezDePa == false) {
			throw new Zend_Exception(get_class($this) . " Pas assez de PA : " . $this->view->user->pa_braldun);
		}
		if (((int)$this->request->get("valeur_1") . "" != $this->request->get("valeur_1") . "")) {
			throw new Zend_Exception(get_class($this) . " Braldûn invalide : " . $this->request->get("valeur_1"));
		} else {
			$idBraldun = (int)$this->request->get("valeur_1");
		}

		if (((int)$this->request->get("valeur_2") . "" != $this->request->get("valeur_2") . "") || !array_key_exists($this->request->get("valeur_2"), $this->view->tabAliments)) {
			throw new Zend_Exception(get_class($this) . " Aliment invalide : " . $this->request->get("valeur_2"));
		} else {
			$idAliment = (int)$this->request->get("valeur_2");
		}

		if ($idBraldun == -1 || $idAliment == -1) {
			throw new Zend_Exception(get_class($this) . " Caract ou Braldûn invalide");
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
			throw new Zend_Exception(get_class($this) . " Braldûn invalide (" . $idBraldun . ")");
		}

		$this->reanimer($braldun, $idAliment);
		$this->setEvenementQueSurOkJet1(false);
		$this->calculPx();
		$this->calculBalanceFaim();
		$this->majBraldun();

		$this->view->braldun = $braldun;
	}

	function reanimer($braldun, $idAliment) {

		$nbReussi = 0;

		$jetBraldun = Bral_Util_De::getLanceDe6($this->view->config->game->base_sagesse + $this->view->user->sagesse_base_braldun);
		$this->view->jetBraldunPourForce = $jetBraldun + $this->view->user->sagesse_bm_braldun + $this->view->user->sagesse_bbdf_braldun;

		$jetForce = Bral_Util_De::getLanceDe6($this->view->config->game->base_force + $braldun["force_base_braldun"]);
		$this->view->jetCibleForce = $jetForce + $braldun["sagesse_bm_braldun"] + $braldun["sagesse_bbdf_braldun"];

		if ($this->view->jetBraldunPourForce > $this->view->jetCibleForce) {
			$nbReussi++;
		}

		$jetBraldun = Bral_Util_De::getLanceDe6($this->view->config->game->base_sagesse + $this->view->user->sagesse_base_braldun);
		$this->view->jetBraldunPourSagesse = $jetBraldun + $this->view->user->sagesse_bm_braldun + $this->view->user->sagesse_bbdf_braldun;

		$jetSagesse = Bral_Util_De::getLanceDe6($this->view->config->game->base_sagesse + $braldun["sagesse_base_braldun"]);
		$this->view->jetCibleSagesse = $jetSagesse + $braldun["sagesse_bm_braldun"] + $braldun["sagesse_bbdf_braldun"];

		if ($this->view->jetBraldunPourSagesse > $this->view->jetCibleSagesse) {
			$nbReussi++;
		}

		$jetBraldun = Bral_Util_De::getLanceDe6($this->view->config->game->base_sagesse + $this->view->user->sagesse_base_braldun);
		$this->view->jetBraldunPourAgilite = $jetBraldun + $this->view->user->sagesse_bm_braldun + $this->view->user->sagesse_bbdf_braldun;

		$jetAgilite = Bral_Util_De::getLanceDe6($this->view->config->game->base_agilite + $braldun["agilite_base_braldun"]);
		$this->view->jetCibleAgilite = $jetAgilite + $braldun["agilite_bm_braldun"] + $braldun["agilite_bbdf_braldun"];

		if ($this->view->jetBraldunPourAgilite > $this->view->jetCibleAgilite) {
			$nbReussi++;
		}

		$jetBraldun = Bral_Util_De::getLanceDe6($this->view->config->game->base_sagesse + $this->view->user->sagesse_base_braldun);
		$this->view->jetBraldunPourVigueur = $jetBraldun + $this->view->user->sagesse_bm_braldun + $this->view->user->sagesse_bbdf_braldun;

		$jetVigueur = Bral_Util_De::getLanceDe6($this->view->config->game->base_vigueur + $braldun["vigueur_base_braldun"]);
		$this->view->jetCibleVigueur = $jetVigueur + $braldun["vigueur_bm_braldun"] + $braldun["vigueur_bbdf_braldun"];

		if ($this->view->jetBraldunPourVigueur > $this->view->jetCibleVigueur) {
			$nbReussi++;
		}

		if ($nbReussi >= 3) {

			$data["est_ko_braldun"] = "non";
			if ($nbReussi == 3) { // -> Le Braldûn est réanimé, sa BdF est à 0 (le repas est quand même consommé : il disparait), ses PV = BM de SAG du lanceur
				$data["pv_restant_braldun"] = $this->view->user->sagesse_bm_braldun + $this->view->user->sagesse_bbdf_braldun;
			} else { // -> Le Braldûn est réanimé, sa BdF remonte de la valeur du repas, ses PV = 2x BM de SAG du lanceur
				$data["balance_faim_braldun"] = $braldun["balance_faim_braldun"] + $this->view->tabAliments[$idAliment]["bbdf"];
				if ($data["balance_faim_braldun"] > 100) {
					$data["balance_faim_braldun"] = 100;
				}
				$this->view->balanceFaim = $data["balance_faim_braldun"];
				$data["pv_restant_braldun"] = 2 * ($this->view->user->sagesse_bm_braldun + $this->view->user->sagesse_bbdf_braldun);
			}

			if ($data["pv_restant_braldun"] < 1) {
				$data["pv_restant_braldun"] = 1;
			}
			if ($data["pv_restant_braldun"] > $braldun["pv_max_braldun"]) {
				$data["pv_restant_braldun"] = $braldun["pv_max_braldun"];
			}

			//le braldun perd ses PX suite au KO
			$data["px_commun_braldun"] = 0;
			$data["px_perso_braldun"] = $braldun["px_perso_braldun"] - floor($braldun["px_perso_braldun"] / 3);

			$this->view->pvRestants = $data["pv_restant_braldun"];
			$data["est_intangible_braldun"] = "oui";
			$data["est_intangible_prochaine_braldun"] = "oui"; // intangible au prochain tour

			$braldunTable = new Braldun();
			$where = "id_braldun = " . $braldun["id_braldun"];
			$braldunTable->update($data, $where);

			Zend_Loader::loadClass("Aliment");
			$alimentTable = new Aliment();
			$where = 'id_aliment = ' . (int)$idAliment;
			$alimentTable->delete($where);

			$id_type = $this->view->config->game->evenements->type->competence;
			$details = "[b" . $this->view->user->id_braldun . "] a réanimé [b" . $braldun["id_braldun"] . "]";
			$messageCible = $this->view->user->prenom_braldun . ' ' . $this->view->user->nom_braldun . ' vous a réanimé ! ' . PHP_EOL;
			$messageCible .= "Vous disposez de " . $this->view->pvRestants . " PV." . PHP_EOL;
			$messageCible .= "Votre balance de faim est de " . $this->view->balanceFaim . " %." . PHP_EOL;
			$this->setDetailsEvenement($details, $id_type);
			$this->setDetailsEvenementCible($braldun["id_braldun"], 'braldun', 0, $messageCible);

			$this->view->okJet1 = true;

		} else { // Si 2 jets ou moins sont supérieurs : échec, le Braldûn ne se réveille pas.
			// rien à faire
		}

		$this->view->nbReussi = $nbReussi;

	}

	function getListBoxRefresh() {
		return $this->constructListBoxRefresh(array("box_vue", "box_laban", "box_charrette"));
	}

}