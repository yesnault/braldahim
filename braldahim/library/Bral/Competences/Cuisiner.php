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
class Bral_Competences_Cuisiner extends Bral_Competences_Competence {

	function prepareCommun() {
		Zend_Loader::loadClass("Laban");
		Zend_Loader::loadClass('Bral_Util_Quete');

		$this->view->cuisinerNbViandeOk = false;
		
		$labanTable = new Laban();
		$laban = $labanTable->findByIdHobbit($this->view->user->id_hobbit);

		// Le joueur tente de transformer n+1 viandes préparées ou n est son niveau de SAG
		$this->view->nbViandePreparee = $this->view->user->sagesse_base_hobbit;

		if ($this->view->nbViandePreparee < 1) {
			$this->view->nbViandePreparee = 1;
		}

		$tabLaban = null;
		foreach ($laban as $p) {
			$tabLaban = array(
				"nb_viande_preparee" => $p["quantite_viande_preparee_laban"],
			);
		}
		if (isset($tabLaban) && $tabLaban["nb_viande_preparee"] > 0) {
			$this->view->cuisinerNbViandeOk = true;
		}

		if ($this->view->nbViandePreparee > $tabLaban["nb_viande_preparee"]) {
			$this->view->nbViandePreparee = $tabLaban["nb_viande_preparee"];
		}

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

		// Verification cuisiner
		if ($this->view->cuisinerNbViandeOk == false) {
			throw new Zend_Exception(get_class($this)." Cuisiner interdit ");
		}

		if ((int)$this->request->get("valeur_1")."" != $this->request->get("valeur_1")."") {
			throw new Zend_Exception(get_class($this)." Nombre invalide");
		} else {
			$nombre = (int)$this->request->get("valeur_1");
		}

		if ($nombre > $this->view->nbViandePreparee) {
			throw new Zend_Exception(get_class($this)." Nombre invalide 2 n:".$nombre. " n1:".$this->view->nbViandePreparee);
		}

		// calcul des jets
		$this->calculJets();

		if ($this->view->okJet1 === true) {
			$this->calculCuisiner($nombre);
			$this->view->estQueteEvenement = Bral_Util_Quete::etapeConstuire($this->view->user, $this->nom_systeme);
		} else {
			$this->calculRateCuisiner($nombre);
		}

		$this->calculPx();
		$this->calculPoids();
		$this->calculBalanceFaim();
		$this->majHobbit();
	}
	
	private function calculRateCuisiner($nombre) {
		$this->view->nbViandePrepareePerdue = floor($nombre / 2);
		
		if ($this->view->nbViandePrepareePerdue < 1) {
			$this->view->nbViandePrepareePerdue = 1;
		}
		
		Zend_Loader::loadClass("Laban");
		$labanTable = new Laban();
		$data = array(
			'id_fk_hobbit_laban' => $this->view->user->id_hobbit,
			'quantite_viande_preparee_laban' => -$this->view->nbViandePrepareePerdue,
		);
		$labanTable->insertOrUpdate($data);
	}

	/*
	 * Transforme 1 unité de viande préparée en 1D2+1 aliment
	 */
	private function calculCuisiner($nombre) {
		Zend_Loader::loadClass("Laban");
		Zend_Loader::loadClass("LabanAliment");
		Zend_Loader::loadClass("TypeAliment");
		Zend_Loader::loadClass("ElementAliment");

		Zend_Loader::loadClass('Bral_Util_Commun');
		$this->view->effetRune = false;

		$this->view->nbAliment = $nombre;
		$this->view->nbViandePreparee = $nombre;

		if (Bral_Util_Commun::isRunePortee($this->view->user->id_hobbit, "RU")) { // s'il possède une rune RU
			$this->view->nbAliment = $this->view->nbAliment + 1;
			$this->view->effetRune = true;
		} else {
			$this->view->nbAliment = $this->view->nbAliment + 0;
		}

		$labanTable = new Laban();
		$data = array(
			'id_fk_hobbit_laban' => $this->view->user->id_hobbit,
			'quantite_viande_preparee_laban' => -$nombre,
		);
		$labanTable->insertOrUpdate($data);

		$poidsRestant = $this->view->user->poids_transportable_hobbit - $this->view->user->poids_transporte_hobbit;
		$poidsRestant = $poidsRestant + (Bral_Util_Poids::POIDS_VIANDE_PREPAREE * $nombre);
		if ($poidsRestant < 0) $poidsRestant = 0;
		$nbAlimentPossible = floor($poidsRestant / Bral_Util_Poids::POIDS_ALIMENT);

		$this->view->nbAlimentATerre = 0;
		if ($this->view->nbAliment > $nbAlimentPossible) {
			$this->view->nbAlimentLaban = $nbAlimentPossible;
			$this->view->nbAlimentATerre = $this->view->nbAliment - $this->view->nbAlimentLaban;
		} else {
			$this->view->nbAlimentLaban = $this->view->nbAliment;
		}

		$this->calculQualite();
		$this->view->qualiteAliment = $this->view->niveauQualite;
		
		$typeAlimentTable = new TypeAliment();
		$aliment = $typeAlimentTable->findById(TypeAliment::ID_TYPE_RAGOUT);
		
		$this->view->typeAliment = $aliment;
		
		$this->view->bbdfAliment = $this->calculBBDF($aliment->bbdf_base_type_aliment, $this->view->niveauQualite);

		$elementAlimentTable = new ElementAliment();
		$labanAlimentTable = new LabanAliment();
		
		Zend_Loader::loadClass("IdsAliment");
		$idsAliment = new IdsAliment();

		for ($i = 1; $i <= $this->view->nbAliment; $i++) {
			
			$id_aliment = $idsAliment->prepareNext();
			
			$data = array(
				"id_element_aliment" => $id_aliment,
				"id_fk_type_element_aliment" => TypeAliment::ID_TYPE_RAGOUT,
				"x_element_aliment" => $this->view->user->x_hobbit,
				"y_element_aliment" => $this->view->user->y_hobbit,
				"id_fk_type_qualite_element_aliment" => $this->view->qualiteAliment,
				"bbdf_element_aliment" => $this->view->bbdfAliment,
			);
			$elementAlimentTable->insert($data);

			if ($i <= $this->view->nbAlimentLaban) {
				$where = "id_element_aliment = ".(int)$id_aliment;
				$elementAlimentTable->delete($where);

				$data = array(
					'id_laban_aliment' => $id_aliment,
					'id_fk_hobbit_laban_aliment' => $this->view->user->id_hobbit,
					'id_fk_type_laban_aliment' => TypeAliment::ID_TYPE_RAGOUT,
					'id_fk_type_qualite_laban_aliment' => $this->view->qualiteAliment,
					'bbdf_laban_aliment' => $this->view->bbdfAliment,
				);
				$labanAlimentTable->insert($data);
			}
		}

		Zend_Loader::loadClass("StatsFabricants");
		$statsFabricants = new StatsFabricants();
		$moisEnCours  = mktime(0, 0, 0, date("m"), 2, date("Y"));
		$dataFabricants["niveau_hobbit_stats_fabricants"] = $this->view->user->niveau_hobbit;
		$dataFabricants["id_fk_hobbit_stats_fabricants"] = $this->view->user->id_hobbit;
		$dataFabricants["mois_stats_fabricants"] = date("Y-m-d", $moisEnCours);
		$dataFabricants["nb_piece_stats_fabricants"] = $this->view->nbAliment;
		$dataFabricants["id_fk_metier_stats_fabricants"] = $this->view->config->game->metier->cuisinier->id;
		$statsFabricants->insertOrUpdate($dataFabricants);
	}

	private function calculQualite() {
		$maitrise = $this->hobbit_competence["pourcentage_hcomp"] / 100;

		$chance_a = -0.375 * $maitrise + 53.75 ;
		$chance_b = 0.25 * $maitrise + 42.5 ;
		$chance_c = 0.125 * $maitrise + 3.75 ;

		/*
		 * Seul le meilleur des n jets est gardé. n=(BM SAG/2)+1.
		 */
		$n = (($this->view->user->sagesse_bm_hobbit + $this->view->user->sagesse_bbdf_hobbit) / 2 ) + 1;

		if ($n < 1) $n = 1;

		$tirage = 0;

		for ($i = 1; $i <= $n; $i ++) {
			$tirageTemp = Bral_Util_De::get_1d100();
			if ($tirageTemp > $tirage) {
				$tirage = $tirageTemp;
			}
		}

		$qualite = -1;
		if ($tirage > 0 && $tirage <= $chance_a) {
			$qualite = 1;
			$this->view->qualite = "frugale";
		} elseif ($tirage > $chance_a && $tirage <= $chance_a + $chance_b) {
			$qualite = 2;
			$this->view->qualite = "correcte";
		} else {
			$qualite = 3;
			$this->view->qualite = "copieuse";
		}
		$this->view->niveauQualite = $qualite;
	}
	
	private function calculBBDF($base, $niveauQualite) {
		$bm = 0;
		/*
		 * Mauvaise : -20%/-10%
		 * Normale : -5%/+10%
		 * Bonne : +15%/+25%
		 */
		if ($niveauQualite == 1) {
			$bm = - Bral_Util_De::get_de_specifique(10, 20);
		} else if ($niveauQualite == 2) {
			$bm = - 5 + Bral_Util_De::get_de_specifique(0, 15);
		} else { // 3
			$bm = Bral_Util_De::get_de_specifique(15, 25);
		}
		return $base + $bm;
	}

	function getListBoxRefresh() {
		if ($this->view->nbAlimentATerre == 0) {
			return $this->constructListBoxRefresh(array("box_competences_metiers", "box_laban"));
		} else {
			return $this->constructListBoxRefresh(array("box_vue", "box_competences_metiers", "box_laban"));
		}
	}
}
