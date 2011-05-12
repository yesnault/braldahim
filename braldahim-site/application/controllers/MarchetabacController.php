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
class MarchetabacController extends Zend_Controller_Action {

	function init() {
		$this->initView();
		$this->view->config = Zend_Registry::get('config');
		Zend_Loader::loadClass('Zend_Filter_StripTags');
		Zend_Loader::loadClass('Bral_Util_ConvertDate');
		Zend_Loader::loadClass('StockTabac');
		Zend_Loader::loadClass('Bral_Xml_GridDhtmlx');
	}

	function indexAction() {
		$this->render();
	}
	
	function renderxmlAction() {
		Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
		Zend_Layout::resetMvcInstance();
		
		Zend_Loader::loadClass('StockTabac');
		
		$stockTabacTable = new StockTabac();
		$stockTabacRowset = $stockTabacTable->findDernierStock();
		
		/*$listeDatesRowset = $stockTabacTable->findDistinctDate();
		foreach($listeDatesRowset as $r) {
			$listeDates[] = $r["date_stock_tabac"];
		}*/
		
		$stocks = null;
		$dhtmlxGrid = new Bral_Xml_GridDhtmlx();
		
		foreach($stockTabacRowset as $r) {
			$stock = array(
				'id_stock_tabac' => $r["id_stock_tabac"],
				'date_stock_tabac' =>  $r["date_stock_tabac"],
				'id_fk_type_stock_tabac' =>  $r["id_fk_type_stock_tabac"],
				'nb_feuille_initial_stock_tabac' =>  $r["nb_feuille_initial_stock_tabac"],
				'nb_feuille_restant_stock_tabac' =>  $r["nb_feuille_restant_stock_tabac"],
				'prix_unitaire_vente_stock_tabac' =>  $r["prix_unitaire_vente_stock_tabac"],
				'prix_unitaire_reprise_stock_tabac' =>  $r["prix_unitaire_reprise_stock_tabac"],
				'id_fk_region_stock_tabac' =>  $r["id_fk_region_stock_tabac"],
				'nom_type_tabac' => $r["nom_type_tabac"],
				'nom_region' => $r["nom_region"],
			);
			
			$tab = null;
			$tab[] = $stock["nom_region"];
			$tab[] = Bral_Util_ConvertDate::get_date_mysql_datetime('d/m/y',$stock["date_stock_tabac"]);
			$tab[] = "<img src='".Zend_Registry::get('config')->url->static."/images/type_tabac/type_tabac_".$stock["id_fk_type_stock_tabac"].".png' alt=\"image\"/>";
			$tab[] = $stock["nom_type_tabac"];
			$tab[] = $stock["prix_unitaire_vente_stock_tabac"];
			$tab[] = $stock["prix_unitaire_reprise_stock_tabac"];
			$tab[] = $stock["nb_feuille_initial_stock_tabac"];
			$tab[] = $stock["nb_feuille_restant_stock_tabac"];
			$dhtmlxGrid->addRow($stock["id_stock_tabac"], $tab);
		}
		
		$this->view->grid = $dhtmlxGrid->render();
	}
}