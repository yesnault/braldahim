<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id$
 * $Author$
 * $LastChangedDate$
 * $LastChangedRevision$
 * $LastChangedBy$
 */
class Bral_Competences_Abattrearbre extends Bral_Competences_Competence {

	function prepareCommun() {
		Zend_Loader::loadClass("Charrette");
		Zend_Loader::loadClass("Zone");
		Zend_Loader::loadClass('Lieu');
		Zend_Loader::loadClass('Echoppe');
		Zend_Loader::loadClass("Bral_Util_Quete");

		$lieuxTable = new Lieu();
		$lieux = $lieuxTable->findByCase($this->view->user->x_hobbit, $this->view->user->y_hobbit);
		unset($lieuxTable);
		$zoneTable = new Zone();
		$zones = $zoneTable->findByCase($this->view->user->x_hobbit, $this->view->user->y_hobbit);
		unset($zoneTable);
		$echoppeTable = new Echoppe();
		$echoppes = $echoppeTable->findByCase($this->view->user->x_hobbit, $this->view->user->y_hobbit);
		unset($echoppeTable);

		$this->view->abattreArbreLieuOk = true;
		$this->view->abattreArbreEchoppeOk = true;

		if (count($lieux) > 0) {
			$this->view->abattreArbreLieuOk = false;
		}
		unset($lieux);

		if (count($echoppes) > 0) {
			$this->view->abattreArbreEchoppeOk = false;
		}
		unset($echoppes);

		$zone = $zones[0];


		switch($zone["nom_systeme_environnement"]) {
			case "foret" :
				$this->view->abattreArbreEnvironnementOk = true;
				break;
			case "marais":
			case "gazon":
			case "montagne":
			case "caverne":
			case "plaine" :
				$this->view->abattreArbreEnvironnementOk = false;
				break;
			default :
				throw new Exception("Abattre un arbre Environnement invalide:".$zone["nom_systeme_environnement"]. " x=".$this->view->user->x_hobbit." y=".$this->view->user->y_hobbit);
		}

		$charretteTable = new Charrette();
		$nombre = $charretteTable->countByIdHobbit($this->view->user->id_hobbit);
		$this->view->charettePleine = true;
		if ($nombre == 1) {
			$this->view->possedeCharrette = true;
				
			$tabPoidsCharrette = Bral_Util_Poids::calculPoidsCharrette($this->view->user->id_hobbit);
			$nbPossibleDansCharretteMaximum = floor($tabPoidsCharrette["place_restante"] / Bral_Util_Poids::POIDS_RONDIN);
			
			if ($nbPossibleDansCharretteMaximum > 0) {
				$this->view->charettePleine = false;
			}
		} else {
			$this->view->possedeCharrette = false;
		}

		unset($zones);
		unset($zone);
	}

	function prepareFormulaire() {
		if ($this->view->assezDePa == false) {
			return;
		}
	}

	function prepareResultat() {
		// Verification des Pa
		if ($this->view->assezDePa == false) {
			throw new Zend_Exception(get_class($this)." Pas assez de PA : ".$this->view->user->pa_hobbit);
		}

		// Verification abattre arbre
		if ($this->view->abattreArbreEnvironnementOk == false || $this->view->abattreArbreLieuOk == false || $this->view->possedeCharrette == false || $this->view->abattreArbreEchoppeOk == false) {
			throw new Zend_Exception(get_class($this)." Abattre un arbre interdit ");
		}

		if ($this->view->charettePleine == true) {
			throw new Zend_Exception(get_class($this)." Charette pleine !");
		}

		// calcul des jets
		$this->calculJets();

		if ($this->view->okJet1 === true) {
			$this->calculAbattreArbre();
		}

		$this->calculPx();
		$this->calculBalanceFaim();
		$this->majHobbit();
	}

	/*
	 * Uniquement utilisable en forêt.
	 * Le Hobbit abat un arbre : il ramasse n rondins (directement dans la charrette).
	 * Le nombre de rondins ramassés est fonction de la VIGUEUR :
	 * de 0 à 4 : 1D3 + BM VIG/2
	 * de 5 à 9 : 2D3 + BM VIG/2
	 * de 10 à 14 :3D3 + BM VIG/2
	 * de 15 à 19 : 4D3 + BM VIG/2
	 * etc ...
	 */
	private function calculAbattreArbre() {
		Zend_Loader::loadClass("Charrette");
		Zend_Loader::loadClass('Bral_Util_Commun');
		Zend_Loader::loadClass('StatsRecolteurs');

		$this->view->effetRune = false;

		$nb = floor($this->view->user->vigueur_base_hobbit / 5) + 1;
		$this->view->nbRondins = Bral_Util_De::getLanceDeSpecifique($nb, 1, 3);

		if (Bral_Util_Commun::isRunePortee($this->view->user->id_hobbit, "VA")) { // s'il possède une rune VA
			$this->view->effetRune = true;
			$this->view->nbRondins = ceil($this->view->nbRondins * 1.5);
		}

		$this->view->nbRondins  = $this->view->nbRondins  + ($this->view->user->vigueur_bm_hobbit + $this->view->user->vigueur_bbdf_hobbit) / 2 ;
		$this->view->nbRondins  = intval($this->view->nbRondins);

		$tabPoidsCharrette = Bral_Util_Poids::calculPoidsCharrette($this->view->user->id_hobbit);
		$nbPossibleDansCharretteMaximum = floor($tabPoidsCharrette["place_restante"] / Bral_Util_Poids::POIDS_RONDIN);

		if ($this->view->nbRondins <= 0) {
			$this->view->nbRondins  = 1;
		}

		if ($this->view->nbRondins > $nbPossibleDansCharretteMaximum) {
			$this->view->nbRondins = $nbPossibleDansCharretteMaximum;
		}

		$charretteTable = new Charrette();
		$data = array(
			'quantite_rondin_charrette' => $this->view->nbRondins,
			'id_fk_hobbit_charrette' => $this->view->user->id_hobbit,
		);
		$charretteTable->updateCharrette($data);
		unset($charretteTable);
		
		Bral_Util_Poids::calculPoidsCharrette($this->view->user->id_hobbit, true);

		$statsRecolteurs = new StatsRecolteurs();
		$moisEnCours  = mktime(0, 0, 0, date("m"), 2, date("Y"));
		$dataRecolteurs["niveau_hobbit_stats_recolteurs"] = $this->view->user->niveau_hobbit;
		$dataRecolteurs["id_fk_hobbit_stats_recolteurs"] = $this->view->user->id_hobbit;
		$dataRecolteurs["mois_stats_recolteurs"] = date("Y-m-d", $moisEnCours);
		$dataRecolteurs["nb_bois_stats_recolteurs"] = $this->view->nbRondins;
		$statsRecolteurs->insertOrUpdate($dataRecolteurs);

		$this->view->estQueteEvenement = Bral_Util_Quete::etapeCollecter($this->view->user, $this->competence["id_fk_metier_competence"]);
	}

	function getListBoxRefresh() {
		return $this->constructListBoxRefresh(array("box_vue", "box_competences_metiers", "box_laban", "box_charrette"));
	}
}