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
class AdministrationstockboisController extends Zend_Controller_Action {

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

	function boisAction() {
		
		$creation = false;
		$demain  = date("Y-m-d H:i:s", mktime(0, 0, 0, date("m")  , date("d")+1, date("Y")));
		$aujourdhui  = date("Y-m-d H:i:s", mktime(0, 0, 0, date("m")  , date("d"), date("Y")));
		
		if ($this->_request->isPost() && $this->_request->getPost('dateStock')) {
			$this->stocksPrepare($this->_request->getPost('dateStock'));
			$this->view->dateStock = $this->_request->getPost('dateStock');
		} else {
			$this->view->dateStock = $aujourdhui;
			$this->stocksPrepare($aujourdhui);
		}
		
		$this->formulairePrepare();

		if ($this->_request->isPost() && $this->_request->getPost('dateStock') == null) {
			Zend_Loader::loadClass('Zend_Filter');
			Zend_Loader::loadClass('Zend_Filter_StripTags');
			Zend_Loader::loadClass('Zend_Filter_StringTrim');

			$creation = true;

			$filter = new Zend_Filter();
			$filter->addFilter(new Zend_Filter_StringTrim())->addFilter(new Zend_Filter_StripTags());
			
			foreach($this->view->idsForm as $f) {
				
				$prixVente = (int)$filter->filter($this->_request->getPost($f."_vente"));
				$prixReprise = (int)$filter->filter($this->_request->getPost($f."_reprise"));
				$nbInitial = (int)$filter->filter($this->_request->getPost($f."_nbinitial"));
	
				if ($prixVente <= 0 || $prixReprise <0 && $nbInitial < 0) {
					throw new Zend_Exception("::boisAction : prixVente(".$prixVente.") ou prixReprise(".$prixReprise.") ou nbInitial(".$nbInitial.") invalide");
				}
				
				$this->ajouteNouveauStock($f, $demain, $prixVente, $prixReprise, $nbInitial);
			}
		}
		
		$this->view->dateCreationStock = $demain;
		$this->view->creation = $creation;
		$this->render();
	}
	
	private function ajouteNouveauStock($idForm, $mdate, $prixVente, $prixReprise, $nbInitial) {
		Zend_Loader::loadClass('StockBois');
		
		$stockBoisTable = new StockBois();
		
		preg_match('/(.*)/', $idForm, $matches);
		$idRegion = $matches[1];
		
		$data = array(
			"date_stock_bois" => $mdate,
			"nb_rondin_initial_stock_bois" => $nbInitial,
			"nb_rondin_restant_stock_bois" => $nbInitial,
			"prix_unitaire_vente_stock_bois" => $prixVente,
			"prix_unitaire_reprise_stock_bois" => $prixReprise,
			"id_fk_region_stock_bois" => $idRegion,	
		);
		
		$stockBoisTable->insert($data);
	}
	
	private function stocksPrepare($mDate) {
		Zend_Loader::loadClass('StockBois');
		
		$stockBoisTable = new StockBois();
		$stockBoisRowset = $stockBoisTable->findByDate($mDate);
		
		$listeDatesRowset = $stockBoisTable->findDistinctDate();
		$listeDates = null;
		foreach($listeDatesRowset as $r) {
			$listeDates[] = $r["date_stock_bois"];
		}
		
		$stocks = null;
		
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
			
			$stocks[] = $stock;
		}
		
		$this->view->stocks = $stocks;
		$this->view->listeDates = $listeDates;
	}
	
	
	private function formulairePrepare() {
		Zend_Loader::loadClass('Region');
		$regionTable = new Region();
		$regionRowset = $regionTable->fetchall();
		$idsForm = null;
		
		foreach($regionRowset as $r) {
			$typeBois = null;
			
			$idForm = $r->id_region;
			$idsForm[] = $idForm;
				
			$regions[] = array(
					"id_region" => $r->id_region,
					"nom_region" => $r->nom_region,
					"id_form" => $idForm,
			);
		}
		
		$this->view->idsForm = $idsForm;
		$this->view->regions = $regions;
	}
}

