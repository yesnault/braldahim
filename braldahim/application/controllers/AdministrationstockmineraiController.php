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
class AdministrationstockmineraiController extends Zend_Controller_Action {

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

	function mineraisAction() {
		
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
					throw new Zend_Exception("::mineraisAction : prixVente(".$prixVente.") ou prixReprise(".$prixReprise.") ou nbInitial(".$nbInitial.") invalide");
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
		Zend_Loader::loadClass('StockMinerai');
		
		$stockMineraiTable = new StockMinerai();
		
		preg_match('/(.*)_(.*)/', $idForm, $matches);
		$idRegion = $matches[1];
		$idTypeMinerai = $matches[2];
		
		$data = array(
			"date_stock_minerai" => $mdate,
			"id_fk_type_stock_minerai" => $idTypeMinerai,
			"nb_brut_initial_stock_minerai" => $nbInitial,
			"nb_brut_restant_stock_minerai" => $nbInitial,
			"prix_unitaire_vente_stock_minerai" => $prixVente,
			"prix_unitaire_reprise_stock_minerai" => $prixReprise,
			"id_fk_region_stock_minerai" => $idRegion,	
		);
		
		$stockMineraiTable->insert($data);
	}
	
	private function stocksPrepare($mDate) {
		Zend_Loader::loadClass('StockMinerai');
		
		$stockMineraiTable = new StockMinerai();
		$stockMineraiRowset = $stockMineraiTable->findByDate($mDate);
		
		$listeDatesRowset = $stockMineraiTable->findDistinctDate();
		
		foreach($listeDatesRowset as $r) {
			$listeDates[] = $r["date_stock_minerai"];
		}
		
		$stocks = null;
		
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
			
			$this->view->regions[$r["id_fk_region_stock_minerai"]]["type_minerais"][$r["id_fk_type_stock_minerai"]]["nb_brut"] = $r["nb_brut_initial_stock_minerai"];
			$this->view->regions[$r["id_fk_region_stock_minerai"]]["type_minerais"][$r["id_fk_type_stock_minerai"]]["prix_unitaire_vente"] = $r["prix_unitaire_vente_stock_minerai"];
			$this->view->regions[$r["id_fk_region_stock_minerai"]]["type_minerais"][$r["id_fk_type_stock_minerai"]]["prix_unitaire_reprise"] = $r["prix_unitaire_reprise_stock_minerai"];
			$stocks[] = $stock;
		}
		
		$this->view->stocks = $stocks;
		$this->view->listeDates = $listeDates;
	}
	
	
	private function formulairePrepare() {
		Zend_Loader::loadClass('Region');
		Zend_Loader::loadClass('TypeMinerai');
		
		$typeMineraiTable = new TypeMinerai();
		$regionTable = new Region();

		$typeMineraiRowset = $typeMineraiTable->fetchall();
		$typeMineraiRowset = $typeMineraiRowset->toArray();
		$regionRowset = $regionTable->fetchall();
		
		$idsForm = null;
		
		foreach($regionRowset as $r) {
			$typeMinerais = null;
			foreach($typeMineraiRowset as $t) {
				$idForm = $r->id_region."_".$t["id_type_minerai"];
				$idsForm[] = $idForm;
				
				$typeMinerais[$t["id_type_minerai"]] = array("id_type_minerai" => $t["id_type_minerai"],
					"nom" => $t["nom_type_minerai"],
					"id_form" => $idForm,
					"nb_brut" => 0,
					"prix_unitaire_vente" => 0,
					"prix_unitaire_reprise" => 0,
				);
			}
			$regions[$r->id_region] = array(
					"id_region" => $r->id_region,
					"nom_region" => $r->nom_region,
					"type_minerais" => $typeMinerais,
			);
		}
		
		$this->view->idsForm = $idsForm;
		$this->view->regions = $regions;
	}
}

