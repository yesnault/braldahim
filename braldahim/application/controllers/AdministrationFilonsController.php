<?php

class AdministrationFilonsController extends Zend_Controller_Action {

	function init() {
		if (!Zend_Auth::getInstance()->hasIdentity()) {
			$this->_redirect('/');
		}
		
		Bral_Util_Securite::controlAdmin();
		
		$this->initView();
		$this->view->user = Zend_Auth::getInstance()->getIdentity();
		$this->view->config = Zend_Registry::get('config');
	}

	function indexAction() {
		$this->render();
	}

	function filonsAction() {
		Zend_Loader::loadClass('Zone');
		Zend_Loader::loadClass('Filon');
		Zend_Loader::loadClass('TypeMinerai');

		$this->filonsPrepare();

		$creation = false;
		$nb_creation = 0;

		if ($this->_request->isPost()) {
			Zend_Loader::loadClass('Zend_Filter');
			Zend_Loader::loadClass('Zend_Filter_StripTags');
			Zend_Loader::loadClass('Zend_Filter_StringTrim');
				
			$creation = true;
				
			$filter = new Zend_Filter();
			$filter->addFilter(new Zend_Filter_StringTrim())->addFilter(new Zend_Filter_StripTags());
			$id_zone = $filter->filter($this->_request->getPost('id_zone'));
			$id_type_minerai = $filter->filter($this->_request->getPost('id_type_minerai_filon'));
			$quantite_min = (int)$filter->filter($this->_request->getPost('quantite_min'));
			$quantite_max = (int)$filter->filter($this->_request->getPost('quantite_max'));
			
			$couverture = $filter->filter($this->_request->getPost('couverture'));
				
			if ($quantite_min == 0 || $quantite_max == 0) {
				throw new Zend_Exception("::FilonsAction : quantite invalide");
			}

			$filonTable = new Filon();

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

				$quantite = Bral_Util_De::get_de_specifique($quantite_min, $quantite_max);
				
				$data = array(
					'id_fk_type_minerai_filon' => $id_type_minerai,
					'x_filon' => $x,
					'y_filon' => $y,
					'quantite_restante_filon' => $quantite,
					'quantite_max_filon' => $quantite,
				);
				$filonTable->insert($data);
			}
		}

		$this->view->creation = $creation;
		$this->view->nb_creation = $nb_creation;
		$this->render();
	}

	private function filonsPrepare() {
		$zoneTable = new Zone();
		$filonTable = new Filon();
		$typeMineraiTable = new TypeMinerai();

		$zonesRowset = $zoneTable->fetchAllAvecEnvironnement();
		$typeMineraiRowset = $typeMineraiTable->fetchAll();

		foreach($zonesRowset as $z) {
			$nombreFilons = $filonTable->countVue($z["x_min_zone"] ,$z["y_min_zone"] ,$z["x_max_zone"] ,$z["y_max_zone"]);
			$nombreCases = ($z["x_max_zone"]  - $z["x_min_zone"] ) * ($z["y_max_zone"]  - $z["y_min_zone"] );
			$couverture = ($nombreFilons * 100) / $nombreCases;
			$zones[] = array("id_zone" =>$z["id_zone"],
				"x_min" =>$z["x_min_zone"] ,
				"x_max" =>$z["x_max_zone"] ,
				"y_min" =>$z["y_min_zone"] ,
				"y_max" =>$z["y_max_zone"] ,
				"environnement" =>$z["nom_environnement"] ,
				"nombre_filons" => $nombreFilons,
				"nombre_cases" => $nombreCases,
				"couverture" => round($couverture)
			);
		}

		foreach($typeMineraiRowset as $t) {
			$typeMinerais[] = array("id_type_minerai" => $t->id_type_minerai,
				"nom" => $t->nom_type_minerai,
				"nom_systeme" => $t->nom_systeme_type_minerai,
				"description" => $t->description_type_minerai,
			);
		}

		$this->view->typeMinerais = $typeMinerais;
		$this->view->zones = $zones;
	}
}

