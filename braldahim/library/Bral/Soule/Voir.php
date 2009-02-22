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
	
	function prepareCommun() {
		Zend_Loader::loadClass('SouleEquipe');
		Zend_Loader::loadClass('SouleMatch');
		Zend_Loader::loadClass('SouleTerrain');
		
		if ($this->request->get("id_terrain") != "") {
			$this->idTerrainEnCours =  Bral_Util_Controle::getValeurIntVerif($this->request->get("id_terrain"));
		}
		if ($this->idTerrainEnCours == null || $this->idTerrainEnCours <= 0) {
			throw new Zend_Exception(get_class($this)." Total trop eleve:".$total_distribution. " c=".$this->view->user->castars_hobbit);
		}
		
		$this->niveauTerrainHobbit = floor($this->view->user->niveau_hobbit/10);
		
		$souleTerrainTable = new SouleTerrain();
		$terrainRowset = $souleTerrainTable->findByIdTerrain($this->idTerrainEnCours);
		$this->view->terrainCourant = $terrainRowset->toArray();
		
		$souleMatchTable = new SouleMatch();
		$this->matchEnCours = $souleMatchTable->findEnCoursByIdTerrain($this->idTerrainEnCours);
		
		$this->calculInscription();
	}

	function prepareFormulaire() {
	}

	function prepareResultat() {
	}

	function getListBoxRefresh() {
	}
	
	private function calculInscription() {
		$this->view->inscriptionPossible = false;
		$this->view->inscriptionNonPossibleInfo = "";
		
		if ($this->niveauTerrainHobbit != $this->view->terrainCourant["niveau_soule_terrain"]) {
			$this->view->inscriptionNonPossibleInfo = "Vous ne pouvez pas vous inscrire sur ce terrain";
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

}