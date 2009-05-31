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
class Bral_Hotel_Voir extends Bral_Hotel_Hotel {

	private $arBoutiqueBruts;
	private $arBoutiqueTransformes;

	function getNomInterne() {
		return "box_lieu";
	}

	public function getTitreAction() {
		return null;
	}
	
	function render() {
		$this->prepareMenu();
		return $this->view->render("hotel/voir.phtml");
	}

	function prepareCommun() {
	}

	function prepareFormulaire() {
	}

	function prepareResultat() {
	}

	function getListBoxRefresh() {
	}
	
	private function prepareMenu() {
		$tabMenu = null;
		
		$tabMenu[] = $this->prepareMenuEquipement();
		$tabMenu[] = array("titre" => "Matières Premières");
		$tabMenu[] = array("titre" => "Matières Transformées");
		$tabMenu[] = array("titre" => "Matériels");
		$tabMenu[] = array("titre" => "Potions");
		$tabMenu[] = array("titre" => "Runes");
		
		$this->view->menuRecherche = $tabMenu;
	}
	
	private function prepareMenuEquipement() {
		$retour = array("titre" => "Équipements");
		
		Zend_Loader::loadClass("TypeEmplacement");
		$typeEmplacementTable = new TypeEmplacement();
		$typesEmplacements = $typeEmplacementTable->fetchAll();
		$typesEmplacements = $typesEmplacements->toArray();
		
		$elements = null;
		foreach($typesEmplacements as $e) {
			$elements[] = array('nom' => $e["nom_type_emplacement"], "table" => "TypeEmplacement", "id_element" => $e["id_type_emplacement"]);
		}
		
		$retour["elements"] = $elements;
		
		return $retour;
	}
}