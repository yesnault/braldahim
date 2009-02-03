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
class MarcheboisController extends Zend_Controller_Action {

	function init() {
		$this->initView();
		$this->view->config = Zend_Registry::get('config');
		Zend_Loader::loadClass('Zend_Filter_StripTags');
		Zend_Loader::loadClass('Bral_Util_ConvertDate');
		Zend_Loader::loadClass('StockBois');
		Zend_Loader::loadClass('Bral_Xml_GridDhtmlx');
	}

	function indexAction() {
		$this->render();
	}
	
	function renderxmlAction() {
		Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
		Zend_Layout::resetMvcInstance();
		
		Zend_Loader::loadClass('StockBois');
		
		$stockBoisTable = new StockBois();
		$stockBoisRowset = $stockBoisTable->findDernierStock();
		
		/*$listeDatesRowset = $stockBoisTable->findDistinctDate();
		foreach($listeDatesRowset as $r) {
			$listeDates[] = $r["date_stock_bois"];
		}*/
		
		$stocks = null;
		$dhtmlxGrid = new Bral_Xml_GridDhtmlx();
		
		foreach($stockBoisRowset as $r) {
			$stock = array(
				'id_stock_bois' => $r["id_stock_bois"],
				'date_stock_bois' =>  $r["date_stock_bois"],
				'nb_rondin_initial_stock_bois' =>  $r["nb_rondin_initial_stock_bois"],
				'nb_rondin_restant_stock_bois' =>  $r["nb_rondin_restant_stock_bois"],
				'prix_unitaire_vente_stock_bois' =>  $r["prix_unitaire_vente_stock_bois"],
				'prix_unitaire_reprise_stock_bois' =>  $r["prix_unitaire_reprise_stock_bois"],
				'id_fk_region_stock_bois' =>  $r["id_fk_region_stock_bois"],
				'nom_region' => $r["nom_region"],
			);
			
			$tab = null;
			$tab[] = $stock["nom_region"];
			$tab[] = Bral_Util_ConvertDate::get_date_mysql_datetime('d/m/y',$stock["date_stock_bois"]);
			$tab[] = "<img src='/public/styles/braldahim_defaut/images/elements/rondin.png' alt='rondin'/>";
			$tab[] = $stock["prix_unitaire_vente_stock_bois"];
			$tab[] = $stock["prix_unitaire_reprise_stock_bois"];
			$tab[] = $stock["nb_rondin_initial_stock_bois"];
			$tab[] = $stock["nb_rondin_restant_stock_bois"];
			$dhtmlxGrid->addRow($stock["id_stock_bois"], $tab);
		}
		
		$this->view->grid = $dhtmlxGrid->render();
	}
}