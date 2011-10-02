<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Competences_Abattrearbre extends Bral_Competences_Competence
{

	function prepareCommun()
	{
		Zend_Loader::loadClass("Charrette");
		Zend_Loader::loadClass("Bosquet");
		Zend_Loader::loadClass("Bral_Util_Quete");

		$bosquetTable = new Bosquet();
		$bosquets = $bosquetTable->findByCase($this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun);

		$bosquet = null;
		$this->view->abattreArbreEnvironnementOk = false;
		if ($bosquets != null) {
			$bosquet = $bosquets[0];
			$this->view->abattreArbreEnvironnementOk = true;
		}
		$this->view->labanPlein = true;
		$poidsRestantLaban = $this->view->user->poids_transportable_braldun - $this->view->user->poids_transporte_braldun;
		$nbPossibleDansLabanMaximum = floor($poidsRestantLaban / Bral_Util_Poids::POIDS_RONDIN);
		if ($nbPossibleDansLabanMaximum > 0) {
			$this->view->labanPlein = false;
		}
		$this->view->nbPossibleDansLabanMax = $nbPossibleDansLabanMaximum;
		$charretteTable = new Charrette();
		$nombre = $charretteTable->countByIdBraldun($this->view->user->id_braldun);
		$this->view->charettePleine = true;
		if ($nombre == 1) {
			$this->view->possedeCharrette = true;

			$tabPoidsCharrette = Bral_Util_Poids::calculPoidsCharrette($this->view->user->id_braldun);
			$nbPossibleDansCharretteMaximum = floor($tabPoidsCharrette["place_restante"] / Bral_Util_Poids::POIDS_RONDIN);

			if ($nbPossibleDansCharretteMaximum > 0) {
				$this->view->charettePleine = false;
			}
			$this->view->nbPossibleDansCharretteMax = $nbPossibleDansCharretteMaximum;
		} else {
			$this->view->possedeCharrette = false;
		}

		$this->view->bosquetCourant = $bosquet;
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

		// Verification abattre arbre
		if ($this->view->abattreArbreEnvironnementOk == false) {
			throw new Zend_Exception(get_class($this) . " Abattre un arbre interdit ");
		}

		// Verification arrivee
		$arrivee = Bral_Util_Controle::getValeurIntVerif($this->request->get("valeur_1"));
		if ($arrivee < 1 || $arrivee > 3) {
			throw new Zend_Exception(get_class($this) . " Destination impossible ");
		}

		if ($this->view->charettePleine == true && $arrivee == 1) {
			throw new Zend_Exception(get_class($this) . " Charette pleine !");
		}

		if ($this->view->possedeCharrette == false && $arrivee == 1) {
			throw new Zend_Exception(get_class($this) . " Pas de charrette !");
		}

		if ($this->view->labanPlein == true && $arrivee == 2) {
			throw new Zend_Exception(get_class($this) . " Laban plein !");
		}

		// calcul des jets
		$this->calculJets();

		if ($this->view->okJet1 === true) {
			$this->calculAbattreArbre($arrivee);
		}

		$this->calculPx();
		$this->calculBalanceFaim();
		$this->calculPoids();
		$this->majBraldun();
	}

	/*
		  * Uniquement utilisable en forêt.
		  * Le Braldûn abat un arbre : il ramasse n rondins (directement dans la charrette).
		  * Le nombre de rondins ramassés est fonction de la VIGUEUR :
		  * de 0 à 4 : 1D3 + BM VIG/2
		  * de 5 à 9 : 2D3 + BM VIG/2
		  * de 10 à 14 :3D3 + BM VIG/2
		  * de 15 à 19 : 4D3 + BM VIG/2
		  * etc ...
		  */
	private function calculAbattreArbre($arrivee)
	{
		Zend_Loader::loadClass('Bral_Util_Commun');
		Zend_Loader::loadClass('StatsRecolteurs');

		$this->view->effetRune = false;

		$nb = floor($this->view->user->vigueur_base_braldun / 5) + 1;
		$this->view->nbRondins = Bral_Util_De::getLanceDeSpecifique($nb, 1, 3);

		if (Bral_Util_Commun::isRunePortee($this->view->user->id_braldun, "VA")) { // s'il possède une rune VA
			$this->view->effetRune = true;
			$this->view->nbRondins = ceil($this->view->nbRondins * 1.5);
		}

		$this->view->nbRondins = $this->view->nbRondins + ($this->view->user->vigueur_bm_braldun + $this->view->user->vigueur_bbdf_braldun) / 2;
		$this->view->nbRondins = intval($this->view->nbRondins);

		if ($this->view->nbRondins <= 0) {
			$this->view->nbRondins = 1;
		}

		$bosquetTable = new Bosquet();
		$where = "id_bosquet=" . $this->view->bosquetCourant["id_bosquet"];
		// Destruction du bosquet s'il ne reste plus rien
		if ($this->view->bosquetCourant["quantite_restante_bosquet"] - $this->view->nbRondins <= 0) {
			$this->view->nbRondins = $this->view->bosquetCourant["quantite_restante_bosquet"];
			$bosquetTable->delete($where);
			$bosquetDetruit = true;
			if ($this->view->bosquetCourant['numero_bosquet'] != null) {
				$bosquetDansNumero = $bosquetTable->findByNumero($this->view->bosquetCourant['numero_bosquet']);
				if ($bosquetDansNumero == null || count($bosquetDansNumero) == 0) {
					// recreation si l'on a supprimé tout le bosquet
					$this->recreation($this->view->bosquetCourant['id_fk_type_bosquet_bosquet']);
				}
			}
		} else {
			$data = array(
				'quantite_restante_bosquet' => $this->view->bosquetCourant["quantite_restante_bosquet"] - $this->view->nbRondins,
			);
			$bosquetTable->update($data, $where);
			$bosquetDetruit = false;
		}

		$aTerre = 0;

		//Charrette
		if ($arrivee == 1) {
			Zend_Loader::loadClass("Charrette");

			$dansCharrette = $this->view->nbRondins;

			if ($dansCharrette > $this->view->nbPossibleDansCharretteMax) {
				$dansCharrette = $this->view->nbPossibleDansCharretteMax;
				$aTerre = $this->view->nbRondins - $dansCharrette;
			}

			$charretteTable = new Charrette();
			$data = array(
				'quantite_rondin_charrette' => $dansCharrette,
				'id_fk_braldun_charrette' => $this->view->user->id_braldun,
			);
			$charretteTable->updateCharrette($data);
			unset($charretteTable);

			Bral_Util_Poids::calculPoidsCharrette($this->view->user->id_braldun, true);
		}

		//Laban
		if ($arrivee == 2) {
			Zend_Loader::loadClass("Laban");
			$dansLaban = $this->view->nbRondins;
			if ($dansLaban > $this->view->nbPossibleDansLabanMax) {
				$dansLaban = $this->view->nbPossibleDansLabanMax;
				$aTerre = $this->view->nbRondins - $dansLaban;
			}

			$labanTable = new Laban();
			$data = array(
				'quantite_rondin_laban' => $dansLaban,
				'id_fk_braldun_laban' => $this->view->user->id_braldun,
			);
			$labanTable->insertOrUpdate($data);
			unset($labanTable);
		}

		//sol
		if ($arrivee == 3) {
			$aTerre = $this->view->nbRondins;
		}

		if ($aTerre > 0) {
			Zend_Loader::loadClass("Element");
			$elementTable = new Element();
			$data = array(
				"quantite_rondin_element" => $aTerre,
				"x_element" => $this->view->user->x_braldun,
				"y_element" => $this->view->user->y_braldun,
				"z_element" => $this->view->user->z_braldun,
			);
			$elementTable->insertOrUpdate($data);
		}

		$this->view->nbRondinsATerre = $aTerre;
		$this->view->arrivee = $arrivee;

		$statsRecolteurs = new StatsRecolteurs();
		$moisEnCours = mktime(0, 0, 0, date("m"), 2, date("Y"));
		$dataRecolteurs["niveau_braldun_stats_recolteurs"] = $this->view->user->niveau_braldun;
		$dataRecolteurs["id_fk_braldun_stats_recolteurs"] = $this->view->user->id_braldun;
		$dataRecolteurs["mois_stats_recolteurs"] = date("Y-m-d", $moisEnCours);
		$dataRecolteurs["nb_bois_stats_recolteurs"] = $this->view->nbRondins;
		$statsRecolteurs->insertOrUpdate($dataRecolteurs);

		$this->view->estQueteEvenement = Bral_Util_Quete::etapeCollecter($this->view->user, $this->competence["id_fk_metier_competence"]);
		$this->view->bosquetDetruit = $bosquetDetruit;
	}

	function getListBoxRefresh()
	{
		return $this->constructListBoxRefresh(array("box_vue", "box_competences", "box_laban", "box_charrette"));
	}

	private function recreation($idTypeBosquet)
	{
		// s'il y a une ville à moins de 25 cases
		Zend_Loader::loadClass('Bral_Util_Ville');
		$bosquetTable = new Bosquet();

		$x = $this->view->user->x_braldun;
		$y = $this->view->user->y_braldun;
		$delta = 15;
		$x = Bral_Util_De::get_de_specifique($x - $delta, $x + $delta);
		$y = Bral_Util_De::get_de_specifique($y - $delta, $y + $delta);

		$ville = Bral_Util_Ville::trouveVilleProche($x, $y, 25);
		if ($ville != null) {
			$delta = 20;
			$xMin = $ville['x_min_ville'] - $delta;
			$yMin = $ville['y_min_ville'] - $delta;
			$xMax = $ville['x_max_ville'] + $delta;
			$yMax = $ville['y_max_ville'] + $delta;
			$nbActuel = $bosquetTable->countVue($xMin, $yMin, $xMax, $yMax, 0);
			if ($nbActuel < 50) {
				$x = Bral_Util_De::get_de_specifique($xMin, $xMax);
				$y = Bral_Util_De::get_de_specifique($yMin, $yMax);
			}
		}

		$quantite = Bral_Util_De::get_de_specifique(5, 15);

		$numeroBosquet = null;

		$nbCasesAutour = Bral_Util_De::get_de_specifique(2, 9);
		for ($j = 0; $j <= $nbCasesAutour; $j++) {
			for ($k = 0; $k <= $nbCasesAutour; $k++) {
				if ($bosquetTable->countByCase($x + $j, $y + $k, 0) == 0) {
					$data = array(
						'id_fk_type_bosquet_bosquet' => $idTypeBosquet,
						'x_bosquet' => $x + $j,
						'y_bosquet' => $y + $k,
						'z_bosquet' => $this->view->user->z_braldun,
						'quantite_restante_bosquet' => $quantite,
						'quantite_max_bosquet' => $quantite,
						'numero_bosquet' => $numeroBosquet,
					);

					$idBosquet = $bosquetTable->insert($data);
					if ($numeroBosquet == null) {
						$numeroBosquet = $idBosquet;
						$where = 'id_bosquet = ' . $idBosquet;
						$data['numero_bosquet'] = $numeroBosquet;
						$bosquetTable->update($data, $where);
					}
				}
			}
		}


	}

}