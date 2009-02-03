<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: MarcheequipementController.php 1121 2009-02-02 18:32:05Z yvonnickesnault $
 * $Author: yvonnickesnault $
 * $LastChangedDate: 2009-02-02 19:32:05 +0100 (Mon, 02 Feb 2009) $
 * $LastChangedRevision: 1121 $
 * $LastChangedBy: yvonnickesnault $
 */
class MarchemineraiController extends Zend_Controller_Action {

	function init() {
		$this->initView();
		$this->view->config = Zend_Registry::get('config');
		Zend_Loader::loadClass('Zend_Filter_StripTags');
		Zend_Loader::loadClass('Bral_Util_ConvertDate');
		Zend_Loader::loadClass('StockMinerai');
		Zend_Loader::loadClass('Bral_Xml_GridDhtmlx');
	}

	function indexAction() {
		$this->render();
	}
	
	function renderxmlAction() {
		Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
		Zend_Layout::resetMvcInstance();
		
		Zend_Loader::loadClass('StockMinerai');
		
		$stockMineraiTable = new StockMinerai();
		$stockMineraiRowset = $stockMineraiTable->findDernierStock();
		
		/*$listeDatesRowset = $stockMineraiTable->findDistinctDate();
		foreach($listeDatesRowset as $r) {
			$listeDates[] = $r["date_stock_minerai"];
		}*/
		
		$stocks = null;
		$dhtmlxGrid = new Bral_Xml_GridDhtmlx();
		
		foreach($stockMineraiRowset as $r) {
			$stock = array(
				'id_stock_minerai' => $r["id_stock_minerai"],
				'date_stock_minerai' =>  $r["date_stock_minerai"],
				'id_fk_type_stock_minerai' =>  $r["id_fk_type_stock_minerai"],
				'nb_brut_initial_stock_minerai' =>  $r["nb_brut_initial_stock_minerai"],
				'nb_brut_restant_stock_minerai' =>  $r["nb_brut_restant_stock_minerai"],
				'prix_unitaire_vente_stock_minerai' =>  $r["prix_unitaire_vente_stock_minerai"],
				'prix_unitaire_reprise_stock_minerai' =>  $r["prix_unitaire_reprise_stock_minerai"],
				'id_fk_region_stock_minerai' =>  $r["id_fk_region_stock_minerai"],
				'nom_type_minerai' => $r["nom_type_minerai"],
				'nom_region' => $r["nom_region"],
			);
			
			$tab = null;
			$tab[] = $stock["nom_region"];
			$tab[] = Bral_Util_ConvertDate::get_date_mysql_datetime('d/m/y',$stock["date_stock_minerai"]);
			$tab[] = "<img src='/public/styles/braldahim_defaut/images/type_minerai/type_minerai_".$stock["id_fk_type_stock_minerai"].".png' alt=\"image\"/>";
			$tab[] = $stock["nom_type_minerai"];
			$tab[] = $stock["prix_unitaire_vente_stock_minerai"];
			$tab[] = $stock["prix_unitaire_reprise_stock_minerai"];
			$tab[] = $stock["nb_brut_initial_stock_minerai"];
			$tab[] = $stock["nb_brut_restant_stock_minerai"];
			$dhtmlxGrid->addRow($stock["id_stock_minerai"], $tab);
		}
		
		$this->view->grid = $dhtmlxGrid->render();
	}
}