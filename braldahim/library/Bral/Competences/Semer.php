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
class Bral_Competences_Semer extends Bral_Competences_Competence {

	function prepareCommun() {
		Zend_Loader::loadClass("Charrette");
		Zend_Loader::loadClass("Champ");

		if ($this->verificationChamp() == false) {
			return null;
		}

		$conteneurLaban = array("id_conteneur" => "laban", "texte_conteneur" => "Dans votre laban", "graines" => $this->prepareTabGraines("laban"));
		$tabConteneurs["laban"] = $conteneurLaban;

		$charretteTable = new Charrette();
		$charrettes = $charretteTable->findByIdBraldun($this->view->user->id_braldun);

		$charrette = null;
		if (count($charrettes) == 1) {
			$charrette = $charrettes[0];
			$conteneurCharrette = array("id_conteneur" => "charrette", "texte_conteneur" => "Dans votre charrette", "graines" => $this->prepareTabGraines("charrette", $charrette["id_charrette"]));

			$tabConteneurs["charrette"] = $conteneurCharrette;
		}

		$this->view->conteneurs = $tabConteneurs;

	}

	private function verificationChamp() {
		$this->view->semerChampOk = false;

		$champTable = new Champ();
		$champs = $champTable->findByCase($this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun, $this->view->user->id_braldun);

		$retour = false;
		if (count($champs) == 1) {
			$this->champ = $champs[0];
			if ($this->champ["phase_champ"] == "jachere") {
				$this->view->semerChampOk = true;
				$this->idChamp = $this->view->champ["id_champ"];
				$retour = true;
			}
		}

		return $retour;
	}

	private function prepareTabGraines($type, $idCharrette = null) {

		if ($type == "laban") {
			Zend_Loader::loadClass("LabanGraine");
			$labanGraineTable = new LabanGraine();
			$graines = $labanGraineTable->findByIdBraldun($this->view->user->id_braldun);
		} else if ($idCharrette != null) {
			Zend_Loader::loadClass("CharretteGraine");
			$charretteGraineTable = new CharretteGraine();
			$graines = $charretteGraineTable->findByIdCharrette($this->view->user->id_braldun);
		} else {
			throw new Zend_Exception("prepareTabGraines invalide");
		}

		$tabRetour = null;
		if ($graines != null && count($graines) > 0) {
			foreach($graines as $g) {

				$possible = false;

				if ($g["quantite_".$type."_graine"] >= 2) {
					$possible = true;
				}

				$tabRetour[$type."-".$g["id_type_graine"]] = array(
					"id_genere" => $type."-".$g["id_type_graine"],
					"id_type_graine" => $g["id_type_graine"],
					"nom_type_graine" => $g["nom_type_graine"],
					"possible" => $possible,
					"quantite" => $g["quantite_".$type."_graine"],
					"prefix" => $g["prefix_type_graine"],
				);

			}
		} else {
			$tabRetour = null;
		}

		return $tabRetour;

	}

	function prepareFormulaire() {
		if ($this->view->assezDePa == false) {
			return;
		}
	}

	function prepareResultat() {
		// Verification des Pa
		if ($this->view->assezDePa == false) {
			throw new Zend_Exception(get_class($this)." Pas assez de PA : ".$this->view->user->pa_braldun);
		}

		// Verification semer
		if ($this->view->semerChampOk == false) {
			throw new Zend_Exception(get_class($this)." Semer Champ interdit");
		}

		$idChoisit = $this->request->get("valeur_1");
		list ($type, $idTypeGraine) = split("-", $idChoisit);

		if ($type != "laban" && $type != "charrette") {
			throw new Zend_Exception(get_class($this)." Type invalide A : ".$idChoisit);
		}

		$idTypeGraine = intval($idTypeGraine);
		if ($idTypeGraine < 1) {
			throw new Zend_Exception(get_class($this)." Id Type invalide B : ".$idChoisit);
		}

		if (array_key_exists($type, $this->view->conteneurs) == false) {
			throw new Zend_Exception(get_class($this)." Id Type invalide C : ".$idChoisit);
		}

		if (array_key_exists($type."-".$idTypeGraine, $this->view->conteneurs[$type]["graines"]) == false) {
			throw new Zend_Exception(get_class($this)." Id Type invalide D : ".$idChoisit);
		}

		// calcul des jets
		$this->calculSemerChamp($type, $idTypeGraine);

		$idType = $this->view->config->game->evenements->type->competence;
		$details = "[b".$this->view->user->id_braldun."] a semé un champ";
		$this->setDetailsEvenement($details, $idType);
		$this->setEvenementQueSurOkJet1(false);

		$this->calculBalanceFaim();
		$this->calculPoids();
		$this->majBraldun();
	}

	private function calculSemerChamp($type, $idTypeGraine, $idCharrette = null) {

		// on retire 2 poignées
		if ($type == "laban") {
			$labanGraineTable = new LabanGraine();
			$data = array(
				'id_fk_braldun_laban_graine' => $this->view->user->id_braldun,
				'id_fk_type_laban_graine' => $idTypeGraine,
				'quantite_laban_graine' => -2,
			);
			$labanGraineTable->insertOrUpdate($data);
		} else if ($idCharrette != null) {
			$charretteGraineTable = new CharretteGraine();
			$data = array(
				'id_fk_charrette_graine' => $idCharrette,
				'id_fk_type_charrette_graine' => $idTypeGraine, 
				'quantite_charrette_graine' => -2,
			);
			$charretteGraineTable->insertOrUpdate($data);
		} else {
			throw new Exception("Erreur calculSemerChamp type:".$type. " idCharrette:".$idCharrette);
		}

		$this->view->nom_graine = $this->view->conteneurs[$type]["graines"][$type."-".$idTypeGraine]["prefix"];
		$this->view->nom_graine .= $this->view->conteneurs[$type]["graines"][$type."-".$idTypeGraine]["nom_type_graine"];
		$this->initialiseChamp($idTypeGraine);

	}

	private function initialiseChamp($idTypeGraine) {

		$quantite = 500;

		Zend_Loader::loadClass("Bral_Helper_Lune");
		$phase = Bral_Helper_Lune::calculPhase();
		if ($phase == Bral_Helper_Lune::PHASE_ASCENDENTE) {
			$quantite = $quantite + ($quantite * 10 / 100); // +10%
		} elseif ($phase == Bral_Helper_Lune::PHASE_DESCENDENTE) {
			$quantite = $quantite - ($quantite * 5 / 100); // -5%
		}

		if ($this->champ["id_fk_type_graine_champ"] == $idTypeGraine) { // meme type de graine
			$quantite = $quantite - ($quantite * 5 / 100); // -5%
		}

		$dateUtilisation = date("Y-m-d 00:00:00");
		
		$champTable = new Champ();
		$data = array(
			'phase_champ' => 'seme',
			'date_seme_champ' => date("Y-m-d H:i:s"),
			'id_fk_type_graine_champ' => $idTypeGraine,
			'deja_recolte_champ' => 'non',
			'date_fin_seme_champ' => Bral_Util_ConvertDate::get_date_add_day_to_date(date('Y-m-d 00:00:00'), 21),
			'date_fin_recolte_champ' => Bral_Util_ConvertDate::get_date_add_day_to_date(date('Y-m-d 00:00:00'), 26),
			'quantite_champ' => $quantite,
			'date_utilisation_champ' => $dateUtilisation,
		);

		$where = 'id_champ='.$this->champ["id_champ"];
		$champTable->update($data, $where);

		Zend_Loader::loadClass("ChampTaupe");
			
		// suppression des anciennes taupes s'il y en a
		$champTaupeTable = new ChampTaupe();
		$where = 'id_fk_champ_taupe='.$this->champ["id_champ"];
		$champTaupeTable->delete($where);

		$this->initialiseTaupes();
	}

	private function initialiseTaupes() {

		//numero_taupe : n°1 et 2 : longueur 4, n°3 et 4: longueur 3, n°5 : longueur 2
		$numero = range(1, 5);
		shuffle($numero);

		$taupes = null;
		foreach ($numero as $n) {
			$taupes[$n]["numero"] = $n;
			if ($n == 1) {
				$taupes[$n]["taille"] = 4;
			} elseif ($n == 2) {
				$taupes[$n]["taille"] = 3;
			} else {
				$taupes[$n]["taille"] = 2;
			}
		}

		$grille = null;
		for($x = 1; $x <= 10; $x++) {
			for($y = 1; $y <= 10; $y++) {
				$grille[$x][$y] = array('libre' => true);
			}
		}

		foreach($taupes as $t) {
			$positionOk = false;
			while ($positionOk != true) {
				usleep(Bral_Util_De::get_de_specifique(1, 300000));
				$x = Bral_Util_De::get_1d10();
				usleep(Bral_Util_De::get_de_specifique(1, 300000));
				$y = Bral_Util_De::get_1d10();
				usleep(Bral_Util_De::get_de_specifique(1, 300000));
				$sens = Bral_Util_De::get_1d2();
				$positionOk = $this->placeTaupe($grille, $t, $x, $y, $sens);
			}
		}

	}

	private function placeTaupe(&$grille, $taupe, $startX, $startY, $sens) {

		// si çà déborde avec les x, y initiaux
		if ($sens == 1) { // horizontal
			if ($startX + $taupe["taille"] > 10 ) {
				$startX = 10 - $taupe["taille"];
			}
		} else { // vertical
			if ($startY + $taupe["taille"] > 10 ) {
				$startY = 10 - $taupe["taille"];
			}
		}
			
		$positionOk = true;
		for($t = 0; $t < $taupe["taille"]; $t++) {
			if ($sens == 1) { // horizontal
				if ($grille[$startX + $t][$startY]['libre'] == false) {
					$positionOk = false;
				}
			} else { // vertical
				if ($grille[$startX][$startY + $t]['libre'] == false) {
					$positionOk = false;
				}
			}
		}

		if ($positionOk) {
			$champTaupeTable = new ChampTaupe();
			for($t = 0; $t < $taupe["taille"]; $t++) {
				if ($sens == 1) { // horizontal
					$grille[$startX + $t][$startY]['libre'] = false;
					$x = $startX + $t;
					$y = $startY;
				} else { // vertical
					$grille[$startX][$startY + $t]['libre'] = false;
					$x = $startX;
					$y = $startY + $t;
				}

				$data = array(
					'numero_champ_taupe' => $taupe["numero"],
					'taille_champ_taupe' => $taupe["taille"],
					'x_champ_taupe' => $x,
					'y_champ_taupe' => $y,
					'etat_champ_taupe' => 'vivant',
					'id_fk_champ_taupe' => $this->champ["id_champ"],
				);
				$champTaupeTable->insert($data);
			}
		}
		return $positionOk;
	}

	public function getIdChampCourant() {
		if (isset($this->idChamp)) {
			return $this->idChamp;
		} else {
			return false;
		}
	}

	function getListBoxRefresh() {
		return $this->constructListBoxRefresh(array("box_vue", "box_competences_communes", "box_laban", "box_charrette", "box_champs"));
	}
}