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
class AdministrationstocktabacController extends Zend_Controller_Action {

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

	function tabacAction() {
		
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
					throw new Zend_Exception("::tabacAction : prixVente(".$prixVente.") ou prixReprise(".$prixReprise.") ou nbInitial(".$nbInitial.") invalide");
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
		Zend_Loader::loadClass('StockTabac');
		
		$stockTabacTable = new StockTabac();
		
		preg_match('/(.*)_(.*)/', $idForm, $matches);
		$idRegion = $matches[1];
		$idTypeTabac = $matches[2];
		
		$data = array(
			"date_stock_tabac" => $mdate,
			"id_fk_type_stock_tabac" => $idTypeTabac,
			"nb_feuille_initial_stock_tabac" => $nbInitial,
			"nb_feuille_restant_stock_tabac" => $nbInitial,
			"prix_unitaire_vente_stock_tabac" => $prixVente,
			"prix_unitaire_reprise_stock_tabac" => $prixReprise,
			"id_fk_region_stock_tabac" => $idRegion,	
		);
		
		$stockTabacTable->insert($data);
	}
	
	private function stocksPrepare($mDate) {
		Zend_Loader::loadClass('StockTabac');
		
		$stockTabacTable = new StockTabac();
		$stockTabacRowset = $stockTabacTable->findByDate($mDate);
		
		$listeDatesRowset = $stockTabacTable->findDistinctDate();
		
		foreach($listeDatesRowset as $r) {
			$listeDates[] = $r["date_stock_tabac"];
		}
		
		$stocks = null;
		
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
			
			$this->view->regions[$r["id_fk_region_stock_tabac"]]["type_tabac"][$r["id_fk_type_stock_tabac"]]["nb_feuille"] = $r["nb_feuille_initial_stock_tabac"];
			$this->view->regions[$r["id_fk_region_stock_tabac"]]["type_tabac"][$r["id_fk_type_stock_tabac"]]["prix_unitaire_vente"] = $r["prix_unitaire_vente_stock_tabac"];
			$this->view->regions[$r["id_fk_region_stock_tabac"]]["type_tabac"][$r["id_fk_type_stock_tabac"]]["prix_unitaire_reprise"] = $r["prix_unitaire_reprise_stock_tabac"];
			$stocks[] = $stock;
		}
		
		$this->view->stocks = $stocks;
		$this->view->listeDates = $listeDates;
	}
	
	
	private function formulairePrepare() {
		Zend_Loader::loadClass('Region');
		Zend_Loader::loadClass('TypeTabac');
		
		$typeTabacTable = new TypeTabac();
		$regionTable = new Region();

		$typeTabacRowset = $typeTabacTable->fetchall();
		$typeTabacRowset = $typeTabacRowset->toArray();
		$regionRowset = $regionTable->fetchall();
		
		$idsForm = null;
		
		foreach($regionRowset as $r) {
			$typeTabacs = null;
			foreach($typeTabacRowset as $t) {
				$idForm = $r->id_region."_".$t["id_type_tabac"];
				$idsForm[] = $idForm;
				
				$typeTabacs[$t["id_type_tabac"]] = array("id_type_tabac" => $t["id_type_tabac"],
					"nom" => $t["nom_type_tabac"],
					"id_form" => $idForm,
					"nb_feuille" => 10000,
					"prix_unitaire_vente" => 10,
					"prix_unitaire_reprise" => 10000,
				);
			}
			$regions[$r->id_region] = array(
					"id_region" => $r->id_region,
					"nom_region" => $r->nom_region,
					"type_tabac" => $typeTabacs,
			);
		}
		
		$this->view->idsForm = $idsForm;
		$this->view->regions = $regions;
	}
}

