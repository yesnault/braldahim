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
abstract class Bral_Palmares_Box {
	
	protected $loadWithBoxes = true;
	
	function __construct($request, $view, $interne, $filtre = 1, $type = null) {
		Zend_Loader::loadClass("Braldun");
		
		$this->_request = $request;
		$this->view = $view;
		$this->view->affichageInterne = $interne;
		$this->view->filtre = $filtre;
		$this->view->type = $type;
		$this->view->afficheMoyenne = false;
		$this->view->afficheMoisEnCours = true;
	}
	
	abstract function getTitreOnglet();
	abstract function getNomInterne();
	abstract function getNomClasse();
	
	public function getChargementInBoxes() {
		return $this->loadWithBoxes;		
	}
	
	abstract function setDisplay($display) ;
	abstract function render() ;
	
	protected function getTabDateFiltre($nbMoisASortir = 0) {
		$tab = null;
		
		$moisPrecedent = mktime(0, 0, 0, date("m")-1, 1,   date("Y"));
		$anneePrecedente  = mktime(0, 0, 0, 1,   1,   date("Y")-1);
		
		$moisEnCours  = mktime(0, 0, 0, date("m"), 1, date("Y"));
		$anneeEnCours  = mktime(0, 0, 0, 1, 1, date("Y"));
		
		$demain  = mktime(0, 0, 0, date("m")-$nbMoisASortir, date("d")+1, date("Y"));

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
				$tab["dateFin"] = date("Y-m-d H:i:s", $demain);
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
	
	protected function getSelectTypeRecolteur($type) {
		$retour = "";
		switch($type) {
			case "mineurs":
				$retour = "Nombre de minerais récoltés";
				break;
			case "herboristes":
				$retour = "Nombre de parties de plantes récoltées";
				break;
			case "chasseurs":
				$retour = "Nombre de viandes et de peaux récoltés";
				break;
			case "bucherons":
				$retour = "Nombre de rondins récoltés";
				break;
		}
		return $retour;
	}
	
	protected function getSelectTypeFabricant($type) {
		$retour = "";
		$this->view->titreColonne3 = "Niveau moyen des pièces créées";
		switch($type) {
			case "menuisiers":
			case "forgerons":
			case "tanneurs":
				$retour = "Nombre de pièces d'équipements fabriquées";
				$this->view->afficheMoyenne = true;
				break;
			case "bucheronspalissades":
				$retour = "Nombre de palissades fabriquées";
				break;
			case "bucheronsroutes":
				$retour = "Nombre de sentiers balisés";
				break;
			case "cuisiniers":
				$retour = "Nombre de rations préparées";
				break;
			case "apothicaires":
				$retour = "Nombre de potions créées";
				$this->view->titreColonne3 = "Niveau moyen des potions créées";
				$this->view->afficheMoyenne = true;
				break;
		}
		return $retour;
	}
}