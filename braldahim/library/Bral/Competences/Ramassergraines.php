<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Competences_Ramassergraines extends Bral_Competences_Competence {

	function prepareCommun() {
		Zend_Loader::loadClass("Charrette");
		Zend_Loader::loadClass("Buisson");
		Zend_Loader::loadClass("Bral_Util_Quete");

		$this->view->placeDispo = false;

		$buissonTable = new Buisson();
		$buissons = $buissonTable->findByCase($this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun);

		$buisson = null;
		$this->view->ramasserGrainesEnvironnementOk = false;
		if ($buissons != null) {
			$buisson = $buissons[0];
			$this->view->ramasserGrainesEnvironnementOk = true;
		}

		$this->view->buissonCourant = $buisson;

		$this->view->idDestinationCourante = $this->request->get("valeur_1");

		$selectedLaban = "";
		$selectedCharrette = "";
		if ($this->view->idDestinationCourante == "laban") {
			$selectedLaban = "selected";
		} else if ($this->view->idDestinationCourante == "charrette") {
			$selectedCharrette = "selected";
		}
		$tabDestinationRamasser[] = array("id_destination" => "laban", "texte" => "votre laban", "selected" => $selectedLaban);

		$charretteTable = new Charrette();
		$charrettes = $charretteTable->findByIdBraldun($this->view->user->id_braldun);

		$charrette = null;
		if (count($charrettes) == 1) {
			$charrette = $charrettes[0];
			$tabDestinationRamasser[] = array("id_destination" => "charrette", "texte" => "votre charrette", "selected" => $selectedCharrette);
		}

		$this->view->destinationRamasser = $tabDestinationRamasser;

		if ($this->view->idDestinationCourante != null) {
			if ($this->view->idDestinationCourante == "charrette" && $charrette != null) {
				$poidsRestant = $charrette["poids_transportable_charrette"] - $charrette["poids_transporte_charrette"];
			} else {
				$poidsRestant = $this->view->user->poids_transportable_braldun - $this->view->user->poids_transporte_braldun;
			}

			if ($poidsRestant < Bral_Util_Poids::POIDS_POIGNEE_GRAINES) {
				$this->view->placeDispo = false;
			} else {
				$this->view->placeDispo = true;
			}
		}

		$this->view->charrette = $charrette;
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

		if ($this->view->ramasserGrainesEnvironnementOk == false || $this->view->placeDispo == false) {
			throw new Zend_Exception(get_class($this)." Ramasser des graines interdit ");
		}

		// calcul des jets
		$this->calculJets();

		if ($this->view->okJet1 === true) {
			$this->calculRamasserGraines();
		}

		$this->calculPx();
		$this->calculBalanceFaim();
		$this->calculPoids();
		$this->majBraldun();
	}

	private function calculRamasserGraines() {
		Zend_Loader::loadClass("Charrette");
		Zend_Loader::loadClass('Bral_Util_Commun');
		Zend_Loader::loadClass('StatsRecolteurs');

		$this->view->nbGraines = 1;

		$buissonTable = new Buisson();
		$where = "id_buisson=".$this->view->buissonCourant["id_buisson"];
		// Destruction du buisson s'il ne reste plus rien
		if ($this->view->buissonCourant["quantite_restante_buisson"] - $this->view->nbGraines  <= 0) {
			$this->view->nbGraines = $this->view->buissonCourant["quantite_restante_buisson"];
			$buissonTable->delete($where);
			$buissonDetruit = true;
			$this->recreation();
		} else {
			$data = array(
				'quantite_restante_buisson' => $this->view->buissonCourant["quantite_restante_buisson"] - $this->view->nbGraines,
			);
			$buissonTable->update($data, $where);
			$buissonDetruit = false;
		}

		if ($this->view->idDestinationCourante == "charrette" && $this->view->charrette != null) {
			Zend_Loader::loadClass("CharretteGraine");
			$charretteGraineTable = new CharretteGraine();
			$data = array(
				'quantite_charrette_graine' => $this->view->nbGraines,
				'id_fk_type_charrette_graine' => $this->view->buissonCourant["id_fk_type_buisson_buisson"],
				'id_fk_charrette_graine' => $this->view->charrette["id_charrette"],
			);
			$charretteGraineTable->insertOrUpdate($data);
			unset($charretteGraineTable);

			Bral_Util_Poids::calculPoidsCharrette($this->view->user->id_braldun, true);
		} else {
			Zend_Loader::loadClass("LabanGraine");
			$labanGraineTable = new LabanGraine();
			$data = array(
				'quantite_laban_graine' => $this->view->nbGraines,
				'id_fk_braldun_laban_graine' => $this->view->user->id_braldun,
				'id_fk_type_laban_graine' => $this->view->buissonCourant["id_fk_type_buisson_buisson"],
			);
			$labanGraineTable->insertOrUpdate($data);
			unset($charretteTable);
		}
			
		$statsRecolteurs = new StatsRecolteurs();
		$moisEnCours  = mktime(0, 0, 0, date("m"), 2, date("Y"));
		$dataRecolteurs["niveau_braldun_stats_recolteurs"] = $this->view->user->niveau_braldun;
		$dataRecolteurs["id_fk_braldun_stats_recolteurs"] = $this->view->user->id_braldun;
		$dataRecolteurs["mois_stats_recolteurs"] = date("Y-m-d", $moisEnCours);
		$dataRecolteurs["nb_graines_stats_recolteurs"] = $this->view->nbGraines;
		$statsRecolteurs->insertOrUpdate($dataRecolteurs);

		$this->view->buissonDetruit = $buissonDetruit;
	}

	private function recreation() {
		// s'il y a une ville à moins de 25 cases
		Zend_Loader::loadClass('Bral_Util_Ville');

		$buissonTable = new Buisson();

		$x = $this->view->user->x_braldun;
		$y = $this->view->user->y_braldun;
		$quantite = 1;
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
			$nbActuel = $buissonTable->countVue($xMin, $yMin, $xMax, $yMax, 0);
			if ($nbActuel < 100) {
				$x = Bral_Util_De::get_de_specifique($xMin, $xMax);
				$y = Bral_Util_De::get_de_specifique($yMin, $yMax);
			}
		}
			
		if ($buissonTable->countByCase($x, $y, 0) > 0) {
			$x = $x - 1;
			if ($buissonTable->countByCase($x, $y, 0) > 0) {
				return;
			}
		}

		$data = array(
			'id_fk_type_buisson_buisson' => $this->view->buissonCourant['id_fk_type_buisson_buisson'], 
			'x_buisson' => $x, 
			'y_buisson' => $y, 
			'z_buisson' => 0, 
			'quantite_restante_buisson' => $quantite, 
			'quantite_max_buisson' => $quantite
		);
		$buissonTable->insert($data);
	}

	function getListBoxRefresh() {
		return $this->constructListBoxRefresh(array("box_vue", "box_competences", "box_laban", "box_charrette"));
	}
}