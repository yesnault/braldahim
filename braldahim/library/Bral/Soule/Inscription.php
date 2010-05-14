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
class Bral_Soule_Inscription extends Bral_Soule_Soule {

	function getNomInterne() {
		return "box_action";
	}

	function getTitreAction() {
		return "Inscription au prochain match de soule";
	}

	function prepareCommun() {
		Zend_Loader::loadClass('SouleEquipe');
		Zend_Loader::loadClass('SouleMatch');
		Zend_Loader::loadClass('SouleTerrain');

		$this->view->inscriptionPossible = false;

		$this->calculNbPa();
		$this->calculNbCastars();

		$this->view->hibernationPrevue = true;
		$this->view->donjonEnCours = false;

		$aujourdhui = date("Y-m-d 0:0:0");

		if ($this->view->user->date_fin_hibernation_braldun == null || $this->view->user->date_fin_hibernation_braldun < $aujourdhui) {
			$this->view->hibernationPrevue = false;
		}


		if ($this->verificationDonjon() == true || $this->view->user->est_donjon_braldun == 'oui') {
			$this->view->donjonEnCours = true;
		}

		if ($this->view->donjonEnCours == true || $this->view->hibernationPrevue == true) {
			return;
		}

		if ($this->view->hibernationPrevue == false && $this->view->assezDePa && $this->view->user->est_engage_braldun == "non") {
			$this->prepareTerrain();
			$this->prepareEquipes();
		}
	}

	private function verificationDonjon() {
		$retour = false;
		Zend_Loader::loadClass("DonjonBraldun");
		$donjonBraldunTable = new DonjonBraldun();
		$braldun = $donjonBraldunTable->findByIdBraldunNonTerminee($this->view->user->id_braldun);

		if ($braldun != null) {
			$retour = true;
		}
		return $retour;
	}

	private function prepareTerrain() {

		$this->niveauTerrainBraldun = floor($this->view->user->niveau_braldun/10);

		$souleTerrainTable = new SouleTerrain();
		$terrainRowset = $souleTerrainTable->findByNiveau($this->niveauTerrainBraldun);
		$this->view->terrainCourant = $terrainRowset;

		if ($this->view->terrainCourant == null) {
			throw new Zend_Exception(get_class($this)." terrain invalide niveau=".$this->niveauTerrainBraldun);
		}

		$souleMatchTable = new SouleMatch();
		$this->matchEnCours = $souleMatchTable->findEnCoursByIdTerrain($this->view->terrainCourant["id_soule_terrain"]);

		if ($this->matchEnCours == null) { // s'il n'y a pas de match en cours
			// on regarde si le joueur n'est pas déjà inscrit
			$souleEquipeTable = new SouleEquipe();
			$nombre = $souleEquipeTable->countNonDebuteByIdBraldun($this->view->user->id_braldun);
			if ($nombre == 0) { // si le joueur n'est pas déjà inscrit
				// on regarde s'il n'y a pas plus de 80 joueurs
				$nombreJoueurs = $souleEquipeTable->countNonDebuteByNiveauTerrain($this->niveauTerrainBraldun);
				if ($nombreJoueurs < $this->view->config->game->soule->max->joueurs) {
					$this->view->inscriptionPossible = true;
				} else {
					throw new Zend_Exception(get_class($this)." trop de joueurs inscrits");
				}
			} else {
				throw new Zend_Exception(get_class($this)." déjà inscrit");
			}
		}
	}

	private function prepareEquipes() {

		$souleEquipeTable = new SouleEquipe();
		$equipesRowset = $souleEquipeTable->countInscritsNonDebuteByNiveauTerrain($this->niveauTerrainBraldun);

		$nbInscritsEquipeA = 0;
		$nbInscritsEquipeB = 0;

		if ($equipesRowset != null) {
			foreach($equipesRowset as $e) {
				if ($e["camp_soule_equipe"] == "a") {
					$nbInscritsEquipeA = $e["nombre"];
				} else {
					$nbInscritsEquipeB = $e["nombre"];
				}
			}
		}

		$equipes = null;

		$inscriptionPossibleEquipeA = false;
		$inscriptionPossibleEquipeB = false;

		if ($nbInscritsEquipeA < $this->view->config->game->soule->max->joueurs / 2) {
			$inscriptionPossibleEquipeA = true;
		}

		if ($nbInscritsEquipeB < $this->view->config->game->soule->max->joueurs / 2) {
			$inscriptionPossibleEquipeB = true;
		}

		if ($inscriptionPossibleEquipeA == false && $inscriptionPossibleEquipeB == false) {
			$this->view->inscriptionPossible = false;
		}

		$equipes[1] = array(
			'nom_equipe' => "&Eacute;quipe A",
			'nb_inscrits' => $nbInscritsEquipeA,
			'inscription_possible' => $inscriptionPossibleEquipeA,
		);
			
		$equipes[2] = array(
			'nom_equipe' => "&Eacute;quipe B",
			'nb_inscrits' => $nbInscritsEquipeB,
			'inscription_possible' => $inscriptionPossibleEquipeB,
		);

		$this->view->tabEquipes = $equipes;
	}

	function prepareFormulaire() {
	}

	function prepareResultat() {

		if ($this->view->inscriptionPossible !== true) {
			throw new Zend_Exception(get_class($this)." Erreur inscriptionPossible == false");
		}

		if (((int)$this->request->get("valeur_1").""!=$this->request->get("valeur_1")."")) {
			throw new Zend_Exception("Bral_Soule_Inscription :: Nombre invalide : ".$this->request->get("valeur_1"));
		} else {
			$idEquipeChoisie = (int)$this->request->get("valeur_1");
		}

		/*
		 if (((int)$this->request->get("valeur_2").""!=$this->request->get("valeur_2")."")) {
			throw new Zend_Exception("Bral_Soule_Inscription :: Nombre invalideb : ".$this->request->get("valeur_2"));
			} else {
			$idChoix = (int)$this->request->get("valeur_2");
			}
			*/

		$idChoix = 1;

		$this->calculInscription($idEquipeChoisie, $idChoix);
		$this->majBraldun();
	}

	public function calculNbPa() {
		$this->view->nb_pa = 1;
		if ($this->view->user->pa_braldun - $this->view->nb_pa < 0) {
			$this->view->assezDePa = false;
		} else {
			$this->view->assezDePa = true;
		}
	}

	public function calculNbCastars() {
		if ($this->view->user->niveau_braldun < 10) {
			$this->view->nb_castars = 0;
		} else {
			$this->view->nb_castars = 10;
		}
		
		if ($this->view->user->castars_braldun - $this->view->nb_castars < 0) {
			$this->view->assezDeCastars = false;
		} else {
			$this->view->assezDeCastars = true;
		}
	}

	private function calculInscription($idEquipeChoisie, $idChoix) {
		$souleMatchTable = new SouleMatch();

		$match = $souleMatchTable->findNonDebuteByIdTerrain($this->view->terrainCourant["id_soule_terrain"]);
		if (count($match) > 1) {
			throw new Zend_Exception("Bral_Soule_Inscription :: Nb Match invalides : ".count($match));
		}

		if ($match == null) { // si le match n'est pas initialisé, on le créé
			$xBallon = floor($this->view->terrainCourant["x_min_soule_terrain"] + ($this->view->terrainCourant["x_max_soule_terrain"] - $this->view->terrainCourant["x_min_soule_terrain"]) / 2);
			$yBallon = floor($this->view->terrainCourant["y_min_soule_terrain"] + ($this->view->terrainCourant["y_max_soule_terrain"] - $this->view->terrainCourant["y_min_soule_terrain"]) / 2);

			$data = array(
				"id_fk_terrain_soule_match" => $this->view->terrainCourant["id_soule_terrain"],
				"date_debut_soule_match" => null,
				"date_fin_soule_match" => null,
				"x_ballon_soule_match" => $xBallon,
				"y_ballon_soule_match" => $yBallon,
			);
			$idMatch = $souleMatchTable->insert($data);
		} else {
			$idMatch = $match[0]["id_soule_match"];
		}

		$souleEquipeTable = new SouleEquipe();

		if ($idEquipeChoisie == 1) {
			$camp = 'a';
		} else {
			$camp = 'b';
		}

		if ($idChoix == 1) {
			$retourXY = "oui";
		} else {
			$retourXY = "non";
		}

		$data = array(
			"id_fk_match_soule_equipe" => $idMatch,
			"date_entree_soule_equipe" => date("Y-m-d H:i:s"),
			"id_fk_braldun_soule_equipe" => $this->view->user->id_braldun,
			"camp_soule_equipe" => $camp,
			"retour_xy_soule_equipe" => $retourXY,
		);
		$souleEquipeTable->insert($data);

		$this->view->user->castars_braldun = $this->view->user->castars_braldun - $this->view->nb_castars;
		if ($this->view->user->castars_braldun < 0) {
			$this->view->user->castars_braldun = 0;
		}

		$details = "[b".$this->view->user->id_braldun."] a pris un ticket pour aller jouer un match sur le ".$this->view->terrainCourant["nom_soule_terrain"];
		$idType = $this->view->config->game->evenements->type->soule;
		$this->setDetailsEvenement($details, $idType);
	}

	function getListBoxRefresh() {
		$tab = array("box_soule", "box_laban");
		return $this->constructListBoxRefresh($tab);
	}
}