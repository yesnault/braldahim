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
		
		$this->calculNbPa();
		
		$this->view->inscriptionPossible = false;
		
		$this->niveauTerrainHobbit = floor($this->view->user->niveau_hobbit/10);
		
		$souleTerrainTable = new SouleTerrain();
		$terrainRowset = $souleTerrainTable->findByNiveau($this->niveauTerrainHobbit);
		$this->view->terrainCourant = $terrainRowset->toArray();
		
		if ($this->view->terrainCourant == null) {
			throw new Zend_Exception(get_class($this)." terrain invalide niveau=".$this->niveauTerrainHobbit);
		}
		
		$souleMatchTable = new SouleMatch();
		$this->matchEnCours = $souleMatchTable->findEnCoursByIdTerrain($this->view->terrainCourant["id_soule_terrain"]);
		
		if ($this->matchEnCours == null) { // s'il n'y a pas de match en cours
			// on regarde si le joueur n'est pas déjà inscrit
			$souleEquipeTable = new SouleEquipe();
			$nombre = $souleEquipeTable->countNonDebuteByIdHobbit($this->view->user->id_hobbit);
			if ($nombre == 0) { // si le joueur n'est pas déjà inscrit
				// on regarde s'il n'y a pas plus de 80 joueurs
				$nombreJoueurs = $souleEquipeTable->countNonDebuteByNiveauTerrain($this->niveauTerrainHobbit);
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

	function prepareFormulaire() {
	}

	function prepareResultat() {
		
		if ($this->view->inscriptionPossible !== true) {
			throw new Zend_Exception(get_class($this)." Erreur inscriptionPossible == false");
		}
		
		$this->calculInscription();
		
	}
	
	public function calculNbPa() {
		if ($this->view->user->pa_hobbit - 4 < 0) {
			$this->view->assezDePa = false;
		} else {
			$this->view->assezDePa = true;
		}
	}
	
	private function calculInscription() {
		
	}
	
	function getListBoxRefresh() {
	}
	
}