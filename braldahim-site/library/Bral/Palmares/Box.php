<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: Box.php 595 2008-11-09 11:21:27Z yvonnickesnault $
 * $Author: yvonnickesnault $
 * $LastChangedDate: 2008-11-09 12:21:27 +0100 (Sun, 09 Nov 2008) $
 * $LastChangedRevision: 595 $
 * $LastChangedBy: yvonnickesnault $
 */
abstract class Bral_Palmares_Box {
	
	protected $loadWithBoxes = true;
	
	function __construct($request, $view, $interne, $filtre = 1, $type = null) {
		Zend_Loader::loadClass("Hobbit");
		
		$this->_request = $request;
		$this->view = $view;
		$this->view->affichageInterne = $interne;
		$this->view->filtre = $filtre;
		$this->view->type = $type;
	}
	
	abstract function getTitreOnglet();
	abstract function getNomInterne();
	abstract function getNomClasse();
	
	public function getChargementInBoxes() {
		return $this->loadWithBoxes;		
	}
	
	abstract function setDisplay($display) ;
	abstract function render() ;
	
	protected function getTabDateFiltre() {
		$tab = null;
		
		$moisPrecedent = mktime(0, 0, 0, date("m")-1, 1,   date("Y"));
		$anneePrecedente  = mktime(0, 0, 0, 1,   1,   date("Y")-1);
		
		$moisEnCours  = mktime(0, 0, 0, date("m"), 1, date("Y"));
		$anneeEnCours  = mktime(0, 0, 0, 1, 1, date("Y"));
		
		$demain  = mktime(0, 0, 0, date("m"), date("d")+1, date("Y"));

		switch($this->view->filtre) {
			case 1: // mois en cours
				$tab["dateDebut"] = date("Y-m-d H:i:s", $moisEnCours);
				$tab["dateFin"] = date("Y-m-d H:i:s", $demain);
				break;
			case 2: // dernier mois
				$tab["dateDebut"] = date("Y-m-d H:i:s", $moisPrecedent);
				$tab["dateFin"] = date("Y-m-d H:i:s", $moisEnCours);
				break;
			case 3: // année en cours
				$tab["dateDebut"] = date("Y-m-d H:i:s", $anneeEnCours);
				$tab["dateFin"] = date("Y-m-d H:i:s");
				break;
			case 4: // année précédente
				$tab["dateDebut"] = date("Y-m-d H:i:s", $anneePrecedente);
				$tab["dateFin"] = date("Y-m-d H:i:s", $anneeEnCours);
				break;
			case 5: // depuis toujours;
				$tab["dateDebut"] = date("2000-1-1 0:0:0");
				$tab["dateFin"] = date("Y-m-d H:i:s", $demain);
				break;
			default:
				throw new Zend_Exception("Filtre invalide: ".$this->view->filtre);
		}
		return $tab;
	}
}