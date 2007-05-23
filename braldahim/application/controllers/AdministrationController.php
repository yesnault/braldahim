<?php

class AdministrationController extends Zend_Controller_Action {

	function init() {
		/** TODO a completer */ 

		if (!Zend_Auth::getInstance()->hasIdentity()) {
			$this->_redirect('/');
		}
		$this->initView();
		$this->view->baseUrl = $this->_request->getBaseUrl();
		$this->view->user = Zend_Auth::getInstance()->getIdentity();
	}

	function indexAction() {
		$this->render();
	}

	function PlantesAction() {
		Zend_Loader::loadClass('Zone');
		Zend_Loader::loadClass('Plante');
		Zend_Loader::loadClass('TypePlante');
   
		$zoneTable = new Zone();
		$planteTable = new Plante();
		$typePlanteTable = new TypePlante();
   
		$zonesRowset = $zoneTable->fetchAllAvecEnvironnement();
		$typePlanteRowset = $typePlanteTable->fetchAllAvecEnvironnement();
   
		foreach($zonesRowset as $z) {
			$nombrePlantes = $planteTable->countVue($z["x_min_zone"] ,$z["y_min_zone"] ,$z["x_max_zone"] ,$z["y_max_zone"]);
			$nombreCases = ($z["x_max_zone"]  - $z["x_min_zone"] ) * ($z["y_max_zone"]  - $z["y_min_zone"] );
			$couverture = ($nombrePlantes * 100) / $nombreCases;
			$zones[] = array("id" =>$z["id"],
			"x_min" =>$z["x_min_zone"] ,
			"x_max" =>$z["x_max_zone"] ,
			"y_min" =>$z["y_min_zone"] ,
			"y_max" =>$z["y_max_zone"] ,
			"environnement" =>$z["nom_environnement"] ,
			"nombre_plantes" => $nombrePlantes,
			"nombre_cases" => $nombreCases,
			"couverture" => $couverture);
		}
   
		foreach($typePlanteRowset as $t) {
			$typePlantes[] = array("id" => $t["id"],
			"nom" => $t["nom_type_plante"],
			"categorie" => $t["categorie_type_plante"],
			"environnement" => $t["nom_environnement"],
			"nom_partie_1" => $t["nom_partie_1_type_plante"],
			"nom_partie_2" => $t["nom_partie_2_type_plante"],
			"nom_partie_3" => $t["nom_partie_3_type_plante"],
			"nom_partie_4" => $t["nom_partie_4_type_plante"],
			);
		}
   
		$this->view->typePlantes = $typePlantes;
		$this->view->zones = $zones;
		$this->render();
	}

}

