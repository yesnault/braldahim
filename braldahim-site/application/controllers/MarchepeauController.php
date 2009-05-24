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
class MarchepeauController extends Zend_Controller_Action {

	function init() {
		$this->initView();
		$this->view->config = Zend_Registry::get('config');
		Zend_Loader::loadClass('Zend_Filter_StripTags');
		Zend_Loader::loadClass('Bral_Util_ConvertDate');
		Zend_Loader::loadClass('StockPeau');
		Zend_Loader::loadClass('Bral_Xml_GridDhtmlx');
	}

	function indexAction() {
		$this->render();
	}
	
	function renderxmlAction() {
		Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
		Zend_Layout::resetMvcInstance();
		
		Zend_Loader::loadClass('StockPeau');
		
		$stockPeauTable = new StockPeau();
		$stockPeauRowset = $stockPeauTable->findDernierStock();
		
		$stocks = null;
		$dhtmlxGrid = new Bral_Xml_GridDhtmlx();
		
		foreach($stockPeauRowset as $r) {
			$stock = array(
				'id_stock_peau' => $r["id_stock_peau"],
				'date_stock_peau' =>  $r["date_stock_peau"],
				'nb_peau_initial_stock_peau' =>  $r["nb_peau_initial_stock_peau"],
				'nb_peau_restant_stock_peau' =>  $r["nb_peau_restant_stock_peau"],
				'prix_unitaire_vente_stock_peau' =>  $r["prix_unitaire_vente_stock_peau"],
				'prix_unitaire_reprise_stock_peau' =>  $r["prix_unitaire_reprise_stock_peau"],
				'id_fk_region_stock_peau' =>  $r["id_fk_region_stock_peau"],
				'nom_region' => $r["nom_region"],
			);
			
			$tab = null;
			$tab[] = $stock["nom_region"];
			$tab[] = Bral_Util_ConvertDate::get_date_mysql_datetime('d/m/y',$stock["date_stock_peau"]);
			$tab[] = "<img src='/public/styles/braldahim_defaut/images/elements/peau.png' alt='peau'/>";
			$tab[] = $stock["prix_unitaire_vente_stock_peau"];
			$tab[] = $stock["prix_unitaire_reprise_stock_peau"];
			$tab[] = $stock["nb_peau_initial_stock_peau"];
			$tab[] = $stock["nb_peau_restant_stock_peau"];
			$dhtmlxGrid->addRow($stock["id_stock_peau"], $tab);
		}
		
		$this->view->grid = $dhtmlxGrid->render();
	}
}