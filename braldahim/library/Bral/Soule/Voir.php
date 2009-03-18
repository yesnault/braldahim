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
class Bral_Soule_Voir extends Bral_Soule_Soule {

	function getNomInterne() {
		return "box_soule_interne";
	}

	function render() {
		return $this->view->render("soule/voir.phtml");
	}

	function getTitreAction() {}
	public function calculNbPa() {}

	function prepareCommun() {
		Zend_Loader::loadClass("Bral_Util_Lien");
		Zend_Loader::loadClass('SouleEquipe');
		Zend_Loader::loadClass('SouleMatch');
		Zend_Loader::loadClass('SouleTerrain');

		if ($this->request->get("id_terrain") != "") {
			$this->idTerrainEnCours =  Bral_Util_Controle::getValeurIntVerif($this->request->get("id_terrain"));
		} else if ($this->idTerrainDefaut != null) {
			$this->idTerrainEnCours =  $this->idTerrainDefaut;
		}
		if ($this->idTerrainEnCours == null || $this->idTerrainEnCours <= 0) {
			throw new Zend_Exception(get_class($this)." idTerrainEnCours null".$this->request->get("id_terrain"));
		}

		$this->niveauTerrainHobbit = floor($this->view->user->niveau_hobbit/10);

		$souleTerrainTable = new SouleTerrain();
		$terrainRowset = $souleTerrainTable->findByIdTerrain($this->idTerrainEnCours);
		$this->view->terrainCourant = $terrainRowset;

		$souleMatchTable = new SouleMatch();

		$matchs = $souleMatchTable->findEnCoursByIdTerrain($this->idTerrainEnCours);
		$this->matchEnCours = null;

		if ($matchs != null) {
			$this->matchEnCours = $matchs[0];
		} else if (count($matchs) > 1) {
			throw new Zend_Exception(" Bral_Soule_Voir - Erreur calcul match en cours. idTerrain:".$this->idTerrainEnCours);
		}
		$this->calculInscription();
		$this->prepareEquipes();
		$this->prepareMatch();
		
		if ($this->matchEnCours != null) { 
			$this->prepareEvenements();
		}
	}

	function prepareFormulaire() {
	}

	function prepareResultat() {
	}

	function getListBoxRefresh() {
	}

	private function prepareEquipes() {
		$equipes["equipea"] = array('nom_equipe' => 'équipe A', "joueurs" => null);
		$equipes["equipeb"] = array('nom_equipe' => 'équipe B', "joueurs" => null);

		$souleEquipeTable = new SouleEquipe();
		if ($this->matchEnCours != null) {
			$joueurs = $souleEquipeTable->findByIdMatch($this->matchEnCours["id_soule_match"]);
			$equipes["equipea"]["nom_equipe"] = $this->matchEnCours["nom_equipea_soule_match"];
			$equipes["equipeb"]["nom_equipe"] = $this->matchEnCours["nom_equipeb_soule_match"];
			
			$equipes["equipea"]["plaquages"] = 0;
			$equipes["equipea"]["plaques"] = 0;
			$equipes["equipeb"]["plaquages"] = 0;
			$equipes["equipeb"]["plaques"] = 0;
			
		} else {
			$joueurs = $souleEquipeTable->findNonDebuteByNiveauTerrain($this->view->terrainCourant["niveau_soule_terrain"]);
		}

		if ($joueurs != null && count($joueurs) > 0) {
			foreach($joueurs as $j) {
				if ($j["camp_soule_equipe"] == 'a') {
					$equipes["equipea"]["joueurs"][] = $j;
					$equipes["equipea"]["plaquages"] = $equipes["equipea"]["plaquages"] + $j["nb_hobbit_plaquage_soule_equipe"];
					$equipes["equipea"]["plaques"] = $equipes["equipea"]["plaques"] + $j["nb_plaque_soule_equipe"];
				} else {
					$equipes["equipeb"]["joueurs"][] = $j;
					$equipes["equipeb"]["plaquages"] = $equipes["equipeb"]["plaquages"] + $j["nb_hobbit_plaquage_soule_equipe"];
					$equipes["equipeb"]["plaques"] = $equipes["equipeb"]["plaques"] + $j["nb_plaque_soule_equipe"];
				}
			}
		}

		$this->view->equipes = $equipes;
	}

	private function calculInscription() {
		$this->view->inscriptionPossible = false;
		$this->view->inscriptionNonPossibleInfo = "";

		if ($this->matchEnCours != null) { // s'il un match en cours
			$this->view->inscriptionNonPossibleInfo = "Il y a un match en cours sur ce terrain";
		} else if ($this->niveauTerrainHobbit != $this->view->terrainCourant["niveau_soule_terrain"]) {
			$this->view->inscriptionNonPossibleInfo = "Vous ne pouvez pas vous inscrire sur ce terrain qui n'est pas de votre niveau";
		} else if ($this->view->user->est_engage_hobbit == "oui") {
			$this->view->inscriptionNonPossibleInfo = "Vous ne pouvez pas vous inscrire, vous êtes engagé";
		} else if ($this->matchEnCours == null) { // s'il n'y a pas de match en cours
			// on regarde si le joueur n'est pas déjà inscrit
			$souleEquipeTable = new SouleEquipe();
			$nombre = $souleEquipeTable->countNonDebuteByIdHobbit($this->view->user->id_hobbit);
			if ($nombre == 0) { // si le joueur n'est pas déjà inscrit
				// on regarde s'il n'y a pas plus de 80 joueurs
				$nombreJoueurs = $souleEquipeTable->countNonDebuteByNiveauTerrain($this->niveauTerrainHobbit);
				if ($nombreJoueurs < $this->view->config->game->soule->max->joueurs) {
					$this->view->inscriptionPossible = true;
				}
			} else {
				$this->view->inscriptionNonPossibleInfo = "Vous êtes déjà inscrit sur ce terrain";
			}
		}
	}

	private function prepareMatch() {
		$porteur = null;
		if ($this->matchEnCours != null && $this->matchEnCours["id_fk_joueur_ballon_soule_match"] != null) {
			$idPorteur = $this->matchEnCours["id_fk_joueur_ballon_soule_match"];
			$hobbitTable = new Hobbit();
			$hobbit = $hobbitTable->findById($idPorteur);
			if ($hobbit != null) {
				$porteur = $hobbit->toArray();
			}
		}

		$this->view->porteur = $porteur;
		$this->view->matchEnCours = $this->matchEnCours;
	}
	
	private function prepareEvenements() {
		Zend_Loader::loadClass("Evenement");
		$evenementTable = new Evenement();
		$rowset = $evenementTable->findByIdMatch($this->matchEnCours["id_soule_match"]);

		$tab = null;
		foreach($rowset as $r) {
			$hobbit = $r["prenom_hobbit"]." ".$r["nom_hobbit"]." (".$r["id_hobbit"].")";
			$tab[] = array ("date_evenement" => Bral_Util_ConvertDate::get_datetime_mysql_datetime('d/m/y à H:i:s ',$r["date_evenement"]),
							"hobbit_evenement" => $hobbit,
							"details_evenement" => $r["details_evenement"]);
		}
		$this->view->evenements = $tab;
	} 
}