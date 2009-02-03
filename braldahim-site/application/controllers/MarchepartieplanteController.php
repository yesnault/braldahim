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
class MarchepartieplanteController extends Zend_Controller_Action {

	function init() {
		$this->initView();
		$this->view->config = Zend_Registry::get('config');
		Zend_Loader::loadClass('Zend_Filter_StripTags');
		Zend_Loader::loadClass('Bral_Util_ConvertDate');
		Zend_Loader::loadClass('StockPartieplante');
		Zend_Loader::loadClass('Bral_Xml_GridDhtmlx');
	}

	function indexAction() {
		$this->render();
	}
	
	function renderxmlAction() {
		Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
		Zend_Layout::resetMvcInstance();
		
		Zend_Loader::loadClass('StockPartieplante');
		
		$stockPartieplanteTable = new StockPartieplante();
		$stockPartieplanteRowset = $stockPartieplanteTable->findDernierStock();
		
		/*$listeDatesRowset = $stockPartieplanteTable->findDistinctDate();
		foreach($listeDatesRowset as $r) {
			$listeDates[] = $r["date_stock_partieplante"];
		}*/
		
		$stocks = null;
		$dhtmlxGrid = new Bral_Xml_GridDhtmlx();
		
		foreach($stockPartieplanteRowset as $r) {
			$stock = array(
				'id_stock_partieplante' => $r["id_stock_partieplante"],
				'date_stock_partieplante' =>  $r["date_stock_partieplante"],
				'id_fk_type_stock_partieplante' =>  $r["id_fk_type_stock_partieplante"],
				'id_fk_type_plante_stock_partieplante' =>  $r["id_fk_type_plante_stock_partieplante"],
				'nb_brut_initial_stock_partieplante' =>  $r["nb_brut_initial_stock_partieplante"],
				'nb_brut_restant_stock_partieplante' =>  $r["nb_brut_restant_stock_partieplante"],
				'prix_unitaire_vente_stock_partieplante' =>  $r["prix_unitaire_vente_stock_partieplante"],
				'prix_unitaire_reprise_stock_partieplante' =>  $r["prix_unitaire_reprise_stock_partieplante"],
				'id_fk_region_stock_partieplante' =>  $r["id_fk_region_stock_partieplante"],
				'nom_type_partieplante' => $r["nom_type_partieplante"],
				'nom_type_plante' => $r["nom_type_plante"],
				'nom_region' => $r["nom_region"],
			);
			
			$tab = null;
			$tab[] = $stock["nom_region"];
			$tab[] = Bral_Util_ConvertDate::get_date_mysql_datetime('d/m/y',$stock["date_stock_partieplante"]);
			$tab[] = $stock["nom_type_plante"];
			$tab[] = "<img src='/public/styles/braldahim_defaut/images/type_partieplante/type_partieplante_".$stock["id_fk_type_stock_partieplante"].".png' alt=\"image\"/>";
			$tab[] = $stock["nom_type_partieplante"];
			$tab[] = $stock["prix_unitaire_vente_stock_partieplante"];
			$tab[] = $stock["prix_unitaire_reprise_stock_partieplante"];
			$tab[] = $stock["nb_brut_initial_stock_partieplante"];
			$tab[] = $stock["nb_brut_restant_stock_partieplante"];
			$dhtmlxGrid->addRow($stock["id_stock_partieplante"], $tab);
		}
		
		$this->view->grid = $dhtmlxGrid->render();
	}
}