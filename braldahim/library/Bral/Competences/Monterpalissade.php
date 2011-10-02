<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Competences_Monterpalissade extends Bral_Competences_Competence
{

	function prepareCommun()
	{
		Zend_Loader::loadClass('Charrette');
		Zend_Loader::loadClass('Echoppe');
		Zend_Loader::loadClass('Lieu');
		Zend_Loader::loadClass('Monstre');
		Zend_Loader::loadClass('Palissade');
		Zend_Loader::loadClass('Route');
		Zend_Loader::loadClass('Nid');
		Zend_Loader::loadClass('Champ');
		Zend_Loader::loadClass('Bral_Util_Quete');
		Zend_Loader::loadClass("Bral_Util_Metier");

		$this->view->monterPalissadeOk = false;
		$this->view->monterPalissadeCharretteOk = false;

		/*
				   * On verifie qu'il y a au moins 2 rondins
				   */
		$charretteTable = new Charrette();
		$charrette = $charretteTable->findByIdBraldun($this->view->user->id_braldun);

		if (!isset($charrette)) {
			return;
		}

		$this->view->nRondins = 0;
		foreach ($charrette as $c) {
			$this->view->nRondins = $c["quantite_rondin_charrette"];
			$this->view->monterPalissadeCharretteOk = true;
			break;
		}

		//(niveau du Braldun/10 + 1)*2
		$niveau = $this->view->user->niveau_braldun;
		$this->view->nRondinsNecessaires = ((floor($niveau / 10) + 1)) * 2;
		$this->view->nRondinsSuffisants = false;

		if ($this->view->nRondins >= $this->view->nRondinsNecessaires) {
			$this->view->nRondinsSuffisants = true;
		}

		$this->distance = 1;
		$this->view->x_min = $this->view->user->x_braldun - $this->distance;
		$this->view->x_max = $this->view->user->x_braldun + $this->distance;
		$this->view->y_min = $this->view->user->y_braldun - $this->distance;
		$this->view->y_max = $this->view->user->y_braldun + $this->distance;

		$lieuxTable = new Lieu();
		$lieux = $lieuxTable->selectVue($this->view->x_min, $this->view->y_min, $this->view->x_max, $this->view->y_max, $this->view->user->z_braldun);
		$echoppeTable = new Echoppe();
		$echoppes = $echoppeTable->selectVue($this->view->x_min, $this->view->y_min, $this->view->x_max, $this->view->y_max, $this->view->user->z_braldun);
		$monstreTable = new Monstre();
		$monstres = $monstreTable->selectVue($this->view->x_min, $this->view->y_min, $this->view->x_max, $this->view->y_max, $this->view->user->z_braldun);
		$palissadeTable = new Palissade();
		$palissades = $palissadeTable->selectVue($this->view->x_min, $this->view->y_min, $this->view->x_max, $this->view->y_max, $this->view->user->z_braldun);
		$braldunTable = new Braldun();
		$bralduns = $braldunTable->selectVue($this->view->x_min, $this->view->y_min, $this->view->x_max, $this->view->y_max, $this->view->user->z_braldun);
		$champTable = new Champ();
		$champs = $champTable->selectVue($this->view->x_min, $this->view->y_min, $this->view->x_max, $this->view->y_max, $this->view->user->z_braldun);

		$routeTable = new Route();
		$routes = $routeTable->selectVue($this->view->x_min, $this->view->y_min, $this->view->x_max, $this->view->y_max, $this->view->user->z_braldun);

		$nidTable = new Nid();
		$nids = $nidTable->selectVue($this->view->x_min, $this->view->y_min, $this->view->x_max, $this->view->y_max, $this->view->user->z_braldun);

		$defautChecked = false;

		for ($j = $this->distance; $j >= -$this->distance; $j--) {
			$change_level = true;
			for ($i = -$this->distance; $i <= $this->distance; $i++) {
				$x = $this->view->user->x_braldun + $i;
				$y = $this->view->user->y_braldun + $j;
				$z = $this->view->user->z_braldun;

				$display = $x;
				$display .= " ; ";
				$display .= $y;

				if (($j == 0 && $i == 0) == false) { // on n'affiche pas de boutons dans la case du milieu
					$valid = true;
				} else {
					$valid = false;
				}

				if ($x < $this->view->config->game->x_min || $x > $this->view->config->game->x_max
					|| $y < $this->view->config->game->y_min || $y > $this->view->config->game->y_max
				) { // on n'affiche pas de boutons dans la case du milieu
					$valid = false;
				}

				foreach ($echoppes as $e) {
					if ($x == $e["x_echoppe"] && $y == $e["y_echoppe"] && $z == $e["z_echoppe"]) {
						$valid = false;
						break;
					}
				}

				foreach ($lieux as $l) {
					if ($x == $l["x_lieu"] && $y == $l["y_lieu"] && $z == $l["z_lieu"]) {
						$valid = false;
						break;
					}
				}

				foreach ($bralduns as $h) {
					if ($x == $h["x_braldun"] && $y == $h["y_braldun"] && $z == $h["z_braldun"]) {
						$valid = false;
						break;
					}
				}

				foreach ($monstres as $m) {
					if ($x == $m["x_monstre"] && $y == $m["y_monstre"] && $z == $m["z_monstre"]) {
						$valid = false;
						break;
					}
				}

				foreach ($palissades as $p) {
					if ($x == $p["x_palissade"] && $y == $p["y_palissade"] && $z == $p["z_palissade"]) {
						$valid = false;
						break;
					}
				}

				foreach ($routes as $r) {
					if ($x == $r["x_route"] && $y == $r["y_route"] && $z == $r["z_route"]) {
						$valid = false;
						break;
					}
				}

				foreach ($nids as $n) {
					if ($x == $n["x_route"] && $y == $n["y_nid"] && $z == $n["z_nid"]) {
						$valid = false;
						break;
					}
				}

				foreach ($champs as $n) {
					if ($x == $n["x_champ"] && $y == $n["y_champ"] && $z == $n["z_champ"]) {
						$valid = false;
						break;
					}
				}

				if ($valid === true && $defautChecked == false) {
					$default = "checked";
					$defautChecked = true;
					$this->view->monterPalissadeOk = true;
				} else {
					$default = "";
				}

				$tab[] = array("x_offset" => $i,
					"y_offset" => $j,
					"default" => $default,
					"display" => $display,
					"change_level" => $change_level, // nouvelle ligne dans le tableau
					"valid" => $valid
				);

				$tabValidation[$i][$j] = $valid;

				if ($change_level) {
					$change_level = false;
				}
			}
		}
		$tabChiffres = array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9);
		$this->view->chiffres = $tabChiffres;
		$this->view->tableau = $tab;
		$this->tableauValidation = $tabValidation;
	}

	function prepareFormulaire()
	{
		if ($this->view->assezDePa == false) {
			return;
		}
	}

	function prepareResultat()
	{
		// Verification des Pa
		if ($this->view->assezDePa == false) {
			throw new Zend_Exception(get_class($this) . " Pas assez de PA : " . $this->view->user->pa_braldun);
		}

		if ($this->view->monterPalissadeOk == false) {
			throw new Zend_Exception(get_class($this) . " Monter Palissade interdit");
		}

		if ($this->view->nRondinsSuffisants == false) {
			throw new Zend_Exception(get_class($this) . " Monter Palissade interdit : rondins insuffisants");
		}

		if ($this->view->monterPalissadeCharretteOk == false) {
			throw new Zend_Exception(get_class($this) . " Monter Palissade interdit : pas de charrette");
		}

		Zend_Loader::loadClass("Bral_Util_Controle");

		$chiffre_1 = Bral_Util_Controle::getValeurIntVerif($this->request->get("valeur_2"));
		$chiffre_2 = Bral_Util_Controle::getValeurIntVerif($this->request->get("valeur_3"));
		$chiffre_3 = Bral_Util_Controle::getValeurIntVerif($this->request->get("valeur_4"));
		$chiffre_4 = Bral_Util_Controle::getValeurIntVerif($this->request->get("valeur_5"));

		// on verifie que l'on peut monter une palissade sur la case
		$x_y = $this->request->get("valeur_1");
		list ($offset_x, $offset_y) = preg_split("/h/", $x_y);
		if ($offset_x < -$this->distance || $offset_x > $this->distance) {
			throw new Zend_Exception(get_class($this) . " MonterPalissade X impossible : " . $offset_x);
		}

		if ($offset_y < -$this->distance || $offset_y > $this->distance) {
			throw new Zend_Exception(get_class($this) . " MonterPalissade Y impossible : " . $offset_y);
		}

		if ($this->tableauValidation[$offset_x][$offset_y] !== true) {
			throw new Zend_Exception(get_class($this) . " MonterPalissade XY impossible : " . $offset_x . $offset_y);
		}

		// calcul des jets
		$this->calculJets();

		if ($this->view->okJet1 === true) {
			$this->calculMonterPalissade($this->view->user->x_braldun + $offset_x, $this->view->user->y_braldun + $offset_y, $chiffre_1, $chiffre_2, $chiffre_3, $chiffre_4);
			$this->view->estQueteEvenement = Bral_Util_Quete::etapeConstuire($this->view->user, $this->nom_systeme);
		}

		$this->calculPx();
		$this->calculPoids();
		Bral_Util_Poids::calculPoidsCharrette($this->view->user->id_braldun, true);
		$this->calculBalanceFaim();
		$this->majBraldun();
	}

	private function calculMonterPalissade($x, $y, $chiffre_1, $chiffre_2, $chiffre_3, $chiffre_4)
	{

		$charretteTable = new Charrette();
		$data = array(
			'quantite_rondin_charrette' => -$this->view->nRondinsNecessaires,
			'id_fk_braldun_charrette' => $this->view->user->id_braldun,
		);
		$charretteTable->updateCharrette($data);
		unset($charretteTable);

		$date_creation = date("Y-m-d H:00:00");
		$nb_jours = Bral_Util_De::getLanceDe6($this->view->config->base_sagesse + $this->view->user->sagesse_base_braldun) + $this->view->user->sagesse_bm_braldun + $this->view->user->sagesse_bbdf_braldun;
		if ($nb_jours < 2) {
			$nb_jours = 2;
		}
		$date_fin = Bral_Util_ConvertDate::get_date_add_day_to_date($date_creation, $nb_jours);

		if ($chiffre_1 == 0 && $chiffre_2 == 0 && $chiffre_3 == 0 && $chiffre_4 == 0) {
			$estPorte = "non";
		} else {
			$estPorte = "oui";
		}

		$data = array(
			"x_palissade" => $x,
			"y_palissade" => $y,
			"z_palissade" => $this->view->user->z_braldun,
			"agilite_palissade" => 0,
			"armure_naturelle_palissade" => $this->view->user->armure_naturelle_braldun * 4,
			"pv_restant_palissade" => $this->view->user->pv_restant_braldun,
			"pv_max_palissade" => $this->view->user->pv_restant_braldun,
			"date_creation_palissade" => $date_creation,
			"date_fin_palissade" => $date_fin,
			"est_portail_palissade" => $estPorte,
			"code_1_palissade" => $chiffre_1,
			"code_2_palissade" => $chiffre_2,
			"code_3_palissade" => $chiffre_3,
			"code_4_palissade" => $chiffre_4,
		);

		$palissadeTable = new Palissade();
		$palissadeTable->insert($data);
		unset($palissadeTable);

		Zend_Loader::loadClass("StatsFabricants");
		$statsFabricants = new StatsFabricants();
		$moisEnCours = mktime(0, 0, 0, date("m"), 2, date("Y"));
		$dataFabricants["niveau_braldun_stats_fabricants"] = $this->view->user->niveau_braldun;
		$dataFabricants["id_fk_braldun_stats_fabricants"] = $this->view->user->id_braldun;
		$dataFabricants["mois_stats_fabricants"] = date("Y-m-d", $moisEnCours);
		$dataFabricants["nb_piece_stats_fabricants"] = 1;
		$dataFabricants["id_fk_metier_stats_fabricants"] = Bral_Util_Metier::METIER_BUCHERON_ID;
		$statsFabricants->insertOrUpdate($dataFabricants);

		$this->view->palissade = $data;
	}

	function getListBoxRefresh()
	{
		return $this->constructListBoxRefresh(array("box_competences", "box_vue", "box_laban", "box_charrette"));
	}
}
