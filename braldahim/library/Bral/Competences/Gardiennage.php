<?php

class Bral_Competences_Gardiennage extends Bral_Competences_Competence {
	
	function prepareCommun() {
		Zend_Loader::loadClass("Gardiennage");
		
		$this->tabJoursDebut = null;
		
		for ($i=1; $i<=10; $i++) {
			$this->tabJoursDebut[] = 
				array("valeur" => date("Y-m-d", mktime(0, 0, 0, date("m")  , date("d")+$i, date("Y"))),
					"affichage" => date("d/m/Y", mktime(0, 0, 0, date("m")  , date("d")+$i, date("Y"))),);
		}
		
		
	}
	
	function prepareFormulaire() {
		$tabGardiens = null;
		$gardiennageTable = new Gardiennage();
		$gardiens = $gardiennageTable->findGardiens($this->view->user->id);
		
		foreach($gardiens as $gardien) {
			$tabGardiens[] = array(
				"id_gardien" => $gardien["id_gardien_gardiennage"], 
				"nom_gardien" => $gardien["nom_hobbit"]);
		}
		
		$this->view->tabJoursDebut = $this->tabJoursDebut;
		$this->view->tabGardiens = $tabGardiens;
	}
	
	function prepareResultat() {
		
	}
	
	function getListBoxRefresh() {
		return null;
	}
	
}