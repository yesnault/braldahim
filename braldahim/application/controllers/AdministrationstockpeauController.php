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
class AdministrationstockpeauController extends Zend_Controller_Action {

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

	function peauxAction() {
		
		$creation = false;
		$demain  = date("Y-m-d H:i:s", mktime(0, 0, 0, date("m")  , date("d")+1, date("Y")));
		$aujourdhui  = date("Y-m-d H:i:s", mktime(0, 0, 0, date("m")  , date("d"), date("Y")));
		
		$this->formulairePrepare();
		
		if ($this->_request->isPost() && $this->_request->getPost('dateStock')) {
			$this->stocksPrepare($this->_request->getPost('dateStock'));
			$this->view->dateStock = $this->_request->getPost('dateStock');
		} else {
			$this->view->dateStock = $aujourdhui;
			$this->stocksPrepare($aujourdhui);
		}
		
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
	
				if ($prixVente < 0 || $prixReprise < 0 && $nbInitial < 0) {
					throw new Zend_Exception("::boisAction : prixVente(".$prixVente.") ou prixReprise(".$prixReprise.") ou nbInitial(".$nbInitial.") invalide");
				}
				
				$this->ajouteNouveauStock($f, $demain, $prixVente, $prixReprise, $nbInitial);
			}
			$this->formulairePrepare();
			$this->view->dateStock = $demain;
			$this->stocksPrepare($demain);
		}
		
		$this->view->dateCreationStock = $demain;
		$this->view->creation = $creation;
		$this->render();
	}
	
	private function ajouteNouveauStock($idForm, $mdate, $prixVente, $prixReprise, $nbInitial) {
		Zend_Loader::loadClass('StockPeau');
		
		$stockPeauTable = new StockPeau();
		
		preg_match('/(.*)/', $idForm, $matches);
		$idRegion = $matches[1];
		
		$data = array(
			"date_stock_peau" => $mdate,
			"nb_peau_initial_stock_peau" => $nbInitial,
			"nb_peau_restant_stock_peau" => $nbInitial,
			"prix_unitaire_vente_stock_peau" => $prixVente,
			"prix_unitaire_reprise_stock_peau" => $prixReprise,
			"id_fk_region_stock_peau" => $idRegion,	
		);
		
		$stockPeauTable->insert($data);
	}
	
	private function stocksPrepare($mDate) {
		Zend_Loader::loadClass('StockPeau');
		
		$stockPeauTable = new StockPeau();
		$stockPeauRowset = $stockPeauTable->findByDate($mDate);
		
		$listeDatesRowset = $stockPeauTable->findDistinctDate();
		$listeDates = null;
		foreach($listeDatesRowset as $r) {
			$listeDates[] = $r["date_stock_peau"];
		}
		
		$stocks = null;
		
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
			$this->view->regions[$r["id_fk_region_stock_peau"]]["nb_peau"] =  $r["nb_peau_restant_stock_peau"];
			$this->view->regions[$r["id_fk_region_stock_peau"]]["prix_unitaire_vente"] =  $r["prix_unitaire_vente_stock_peau"];
			$this->view->regions[$r["id_fk_region_stock_peau"]]["prix_unitaire_reprise"] =  $r["prix_unitaire_reprise_stock_peau"];
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
			$typePeau = null;
			
			$idForm = $r->id_region;
			$idsForm[] = $idForm;
				
			$regions[$r->id_region] = array(
					"id_region" => $r->id_region,
					"nom_region" => $r->nom_region,
					"nb_peau" => 0,
					"prix_unitaire_vente" => 0,
					"prix_unitaire_reprise" => 0, 
					"id_form" => $idForm,
			);
		}
		
		$this->view->idsForm = $idsForm;
		$this->view->regions = $regions;
	}
}

