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
class Bral_Box_Soule extends Bral_Box_Box {

	function getTitreOnglet() {
		return "Soule";
	}

	function getNomInterne() {
		return "box_soule";
	}

	function getChargementInBoxes() {
		return false;
	}
	
	function setDisplay($display) {
		$this->view->display = $display;
	}

	function render() {
		if ($this->view->affichageInterne) {
			$this->data();
		}
		$this->view->nom_interne = $this->getNomInterne();
		return $this->view->render("interface/soule.phtml");
	}
	
	private function data() {
		Zend_Loader::loadClass('SouleEquipe');
		Zend_Loader::loadClass('SouleMatch');
		Zend_Loader::loadClass('SouleTerrain');
		
		$this->prepareTerrains();
	}
	
	private function prepareTerrains() {
		$souleTerrainTable = new SouleTerrain();
		$terrainsRowset = $souleTerrainTable->fetchAll();
		
		$terrains = null;
		$terrainBraldun = null;
		$niveauTerrainBraldun = floor($this->view->user->niveau_braldun/10);
		$idTerrainDefaut = null;
		
		if ($terrainsRowset != null && count($terrainsRowset) > 0) {
			foreach($terrainsRowset as $t) {
				$terrain = array(
					"id_soule_terrain" => $t["id_soule_terrain"],
					"nom_soule_terrain" => $t["nom_soule_terrain"],
					"info_soule_terrain" => $t["info_soule_terrain"],
					"niveau_soule_terrain" => $t["niveau_soule_terrain"],
					"selected" => false,
				);
				
				if ($niveauTerrainBraldun == $t["niveau_soule_terrain"] || ($niveauTerrainBraldun >= 3 && $t["niveau_soule_terrain"] == 3)) {
					$terrainBraldun = $terrain;
					$terrain["selected"] = true;
					$idTerrainDefaut = $t["id_soule_terrain"];
				}
				$terrains[] = $terrain;
			}
		}
		
		$this->view->terrains = $terrains;
		$this->view->terrainBraldun = $terrainBraldun;
		
		Zend_Loader::loadClass("Bral_Soule_Factory");
		$voir = Bral_Soule_Factory::getVoir($this->_request, $this->view, $idTerrainDefaut);
		$this->view->htmlTerrain = $voir->render();
	}
}
