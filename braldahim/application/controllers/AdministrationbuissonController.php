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
class AdministrationbuissonController extends Zend_Controller_Action {
	
	function init() {
		if (!Zend_Auth::getInstance()->hasIdentity()) {
			$this->_redirect('/');
		}
		
		Zend_Loader::loadClass("Bral_Util_Securite");
		Bral_Util_Securite::controlAdmin();
		
		$this->initView();
		$this->view->user = Zend_Auth::getInstance()->getIdentity();
		$this->view->config = Zend_Registry::get('config');
	}
	
	function indexAction() {
		$this->render();
	}
	
	function buissonsAction() {
		Zend_Loader::loadClass('Zone');
		Zend_Loader::loadClass('Buisson');
		Zend_Loader::loadClass('TypeBuisson');
		
		$this->buissonsPrepare();
		
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
			$id_type_buisson = $filter->filter($this->_request->getPost('id_type_buisson_buisson'));
			$quantite_min = (int)$filter->filter($this->_request->getPost('quantite_min'));
			$quantite_max = (int)$filter->filter($this->_request->getPost('quantite_max'));
			
			$couverture = $filter->filter($this->_request->getPost('couverture'));
			
			if ($quantite_min == 0 || $quantite_max == 0) {
				throw new Zend_Exception("::BuissonsAction : quantite invalide");
			}
			
			$buissonTable = new Buisson();
			
			foreach ($this->view->zones as $t) {
				if ($t ["id_zone"] == $id_zone) {
					$zone = $t;
					break;
				}
			}
			
			$nb_cases = $t ["nombre_cases"];
			$nb_creation = (int)(($nb_cases * $couverture) / 100);
			for($i = 1; $i <= $nb_creation; $i++) {
				$x = Bral_Util_De::get_de_specifique($zone ["x_min"], $zone ["x_max"]);
				$y = Bral_Util_De::get_de_specifique($zone ["y_min"], $zone ["y_max"]);
				
				$quantite = Bral_Util_De::get_de_specifique($quantite_min, $quantite_max);
				
				$data = array('id_fk_type_buisson_buisson' => $id_type_buisson, 'x_buisson' => $x, 'y_buisson' => $y, 'quantite_restante_buisson' => $quantite, 'quantite_max_buisson' => $quantite);
				$buissonTable->insert($data);
			}
		}
		
		$this->view->creation = $creation;
		$this->view->nb_creation = $nb_creation;
		$this->render();
	}
	
	private function buissonsPrepare() {
		$zoneTable = new Zone();
		$buissonTable = new Buisson();
		$typeBuissonTable = new TypeBuisson();
		
		$zonesRowset = $zoneTable->fetchAllAvecEnvironnement();
		$typeBuissonRowset = $typeBuissonTable->fetchAll();
		
		foreach ($zonesRowset as $z) {
			$nombreBuissons = $buissonTable->countVue($z ["x_min_zone"], $z ["y_min_zone"], $z ["x_max_zone"], $z ["y_max_zone"], 0);
			$nombreCases = ($z ["x_max_zone"] - $z ["x_min_zone"]) * ($z ["y_max_zone"] - $z ["y_min_zone"]);
			$couverture = ($nombreBuissons * 100) / $nombreCases;
			$zones [] = array("id_zone" => $z ["id_zone"], "x_min" => $z ["x_min_zone"], "x_max" => $z ["x_max_zone"], "y_min" => $z ["y_min_zone"], "y_max" => $z ["y_max_zone"], "environnement" => $z ["nom_environnement"], "nombre_buissons" => $nombreBuissons, "nombre_cases" => $nombreCases, "couverture" => round($couverture));
		}
		
		foreach ($typeBuissonRowset as $t) {
			$typeBuissons [] = array("id_type_buisson" => $t->id_type_buisson, "nom" => $t->nom_type_buisson, "nom_systeme" => $t->nom_systeme_type_buisson, "description" => $t->description_type_buisson);
		}
		
		$this->view->typeBuissons = $typeBuissons;
		$this->view->zones = $zones;
	}
}

