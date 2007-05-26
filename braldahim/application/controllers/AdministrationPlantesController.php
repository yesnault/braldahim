<?php

class AdministrationPlantesController extends Zend_Controller_Action {

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
			
		$this->plantesPrepare();

		$creation = false;
		$nb_creation = 0;

		if ($this->_request->isPost()) {
			Zend_Loader::loadClass("Bral_Util_De");
			Zend_Loader::loadClass('Zend_Filter');
			Zend_Loader::loadClass('Zend_Filter_StripTags');
			Zend_Loader::loadClass('Zend_Filter_StringTrim');
				
			$creation = true;
				
			$filter = new Zend_Filter();
			$filter->addFilter(new Zend_Filter_StringTrim())->addFilter(new Zend_Filter_StripTags());
			$id_zone = $filter->filter($this->_request->getPost('id_zone'));
			$id_type_plante = $filter->filter($this->_request->getPost('id_type_plante'));
			$partie_1a = (int)$filter->filter($this->_request->getPost('partie_1a'));
			$partie_1b = (int)$filter->filter($this->_request->getPost('partie_1b'));
			$partie_2a = (int)$filter->filter($this->_request->getPost('partie_2a'));
			$partie_2b = (int)$filter->filter($this->_request->getPost('partie_2b'));
			$partie_3a = (int)$filter->filter($this->_request->getPost('partie_3a'));
			$partie_3b = (int)$filter->filter($this->_request->getPost('partie_3b'));
			$partie_4a = (int)$filter->filter($this->_request->getPost('partie_4a'));
			$partie_4b = (int)$filter->filter($this->_request->getPost('partie_4b'));
			$couverture = $filter->filter($this->_request->getPost('couverture'));
				
			if ($partie_1a == 0 || $partie_1b == 0) {
				throw new Zend_Exception("::PlantesAction : partie 1 min invalide");
			}

			$planteTable = new Plante();

			foreach ($this->view->zones as $t) {
				if ($t["id_zone"] == $id_zone) {
					$zone = $t;
					break;
				}
			}

			$nb_cases = $t["nombre_cases"];
			$nb_creation = (int)(($nb_cases * $couverture) / 100);

			for ($i=1; $i<= $nb_creation; $i++) {
				$x = Bral_Util_De::get_de_specifique($zone["x_min"], $zone["x_max"]);
				$y = Bral_Util_De::get_de_specifique($zone["y_min"], $zone["y_max"]);

				$partie_1 = Bral_Util_De::get_de_specifique($partie_1a, $partie_1b);
					
				if ($partie_2a == 0 || $partie_2b == 0) {
					$partie_2 = null;
				} else {
					$partie_2 = Bral_Util_De::get_de_specifique($partie_2a, $partie_2b);
				}
				if ($partie_3a == 0 || $partie_3b == 0) {
					$partie_3 = null;
				} else {
					$partie_3 = Bral_Util_De::get_de_specifique($partie_3a, $partie_3b);
				}
				if ($partie_4a == 0 || $partie_4 == 0) {
					$partie_4 = null;
				} else {
					$partie_4 = Bral_Util_De::get_de_specifique($partie_4a, $partie_4b);
				}
				$data = array(
				'id_fk_type_plante' => $id_type_plante,
				'x_plante' => $x,
				'y_plante' => $y,
				'partie_1_plante' => $partie_1,
				'partie_2_plante' => $partie_2,
				'partie_3_plante' => $partie_3,
				'partie_4_plante' => $partie_4,
				);

				$planteTable->insert($data);
			}
		}

		$this->view->creation = $creation;
		$this->view->nb_creation = $nb_creation;
		$this->render();
	}

	private function plantesPrepare() {

		$zoneTable = new Zone();
		$planteTable = new Plante();
		$typePlanteTable = new TypePlante();

		$zonesRowset = $zoneTable->fetchAllAvecEnvironnement();
		$typePlanteRowset = $typePlanteTable->fetchAllAvecEnvironnement();

		foreach($zonesRowset as $z) {
			$nombrePlantes = $planteTable->countVue($z["x_min_zone"] ,$z["y_min_zone"] ,$z["x_max_zone"] ,$z["y_max_zone"]);
			$nombreCases = ($z["x_max_zone"]  - $z["x_min_zone"] ) * ($z["y_max_zone"]  - $z["y_min_zone"] );
			$couverture = ($nombrePlantes * 100) / $nombreCases;
			$zones[] = array("id_zone" =>$z["id_zone"],
			"x_min" =>$z["x_min_zone"] ,
			"x_max" =>$z["x_max_zone"] ,
			"y_min" =>$z["y_min_zone"] ,
			"y_max" =>$z["y_max_zone"] ,
			"environnement" =>$z["nom_environnement"] ,
			"nombre_plantes" => $nombrePlantes,
			"nombre_cases" => $nombreCases,
			"couverture" => round($couverture));
		}

		foreach($typePlanteRowset as $t) {
			$typePlantes[] = array("id" => $t["id_type_plante"],
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
	}
}

