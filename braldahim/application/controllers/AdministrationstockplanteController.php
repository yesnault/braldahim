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
class AdministrationstockplanteController extends Zend_Controller_Action {

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

	function plantesAction() {
		
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
	
				if ($prixVente <= 0 || $prixReprise <0 && $nbInitial < 0) {
					throw new Zend_Exception("::PlantesAction : prixVente(".$prixVente.") ou prixReprise(".$prixReprise.") ou nbInitial(".$nbInitial.") invalide");
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
		Zend_Loader::loadClass('StockPartieplante');
		
		$stockPartieplanteTable = new StockPartieplante();
		
		preg_match('/(.*)_(.*)_(.*)/', $idForm, $matches);
		$idRegion = $matches[1];
		$idTypePlante = $matches[2];
		$idTypePartiePlante = $matches[3];
		
		$data = array(
			"date_stock_partieplante" => $mdate,
			"id_fk_type_stock_partieplante" => $idTypePartiePlante,
			"id_fk_type_plante_stock_partieplante" => $idTypePlante,
			"nb_brut_initial_stock_partieplante" => $nbInitial,
			"nb_brut_restant_stock_partieplante" => $nbInitial,
			"prix_unitaire_vente_stock_partieplante" => $prixVente,
			"prix_unitaire_reprise_stock_partieplante" => $prixReprise,
			"id_fk_region_stock_partieplante" => $idRegion,	
		);
		
		$stockPartieplanteTable->insert($data);
		
	}
	
	private function stocksPrepare($mDate) {
		Zend_Loader::loadClass('StockPartieplante');
		
		$stockPartieplanteTable = new StockPartieplante();
		$stockPartieplanteRowset = $stockPartieplanteTable->findByDate($mDate);
		
		$listeDatesRowset = $stockPartieplanteTable->findDistinctDate();
		
		foreach($listeDatesRowset as $r) {
			$listeDates[] = $r["date_stock_partieplante"];
		}
		
		$stocks = null;
		
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
			
			$this->view->regions[$r["id_fk_region_stock_partieplante"]]["type_plantes"][$r["id_fk_type_plante_stock_partieplante"]]["parties"][$r["id_fk_type_stock_partieplante"]]["nb_brut"] = $r["nb_brut_initial_stock_partieplante"];
			$this->view->regions[$r["id_fk_region_stock_partieplante"]]["type_plantes"][$r["id_fk_type_plante_stock_partieplante"]]["parties"][$r["id_fk_type_stock_partieplante"]]["prix_unitaire_vente"] = $r["prix_unitaire_vente_stock_partieplante"];
			$this->view->regions[$r["id_fk_region_stock_partieplante"]]["type_plantes"][$r["id_fk_type_plante_stock_partieplante"]]["parties"][$r["id_fk_type_stock_partieplante"]]["prix_unitaire_reprise"] = $r["prix_unitaire_reprise_stock_partieplante"];
			$stocks[] = $stock;
		}
		
		$this->view->stocks = $stocks;
		$this->view->listeDates = $listeDates;
	}
	
	
	private function formulairePrepare() {
		Zend_Loader::loadClass('Region');
		Zend_Loader::loadClass('TypePlante');
		Zend_Loader::loadClass('TypePartieplante');
		
		$typePlanteTable = new TypePlante();
		$typePartiePlanteTable = new TypePartieplante();
		$regionTable = new Region();

		$typePlanteRowset = $typePlanteTable->fetchAllAvecEnvironnement();
		$typePartiePlanteRowset = $typePartiePlanteTable->fetchall();
		$regionRowset = $regionTable->fetchall();
		
		foreach($typePartiePlanteRowset as $p) {
			$tabPartiePlante[$p->id_type_partieplante]["id"] = $p->id_type_partieplante;
			$tabPartiePlante[$p->id_type_partieplante]["nom"] = $p->nom_type_partieplante;
			$tabPartiePlante[$p->id_type_partieplante]["nom_systeme"] = $p->nom_systeme_type_partieplante;
			$tabPartiePlante[$p->id_type_partieplante]["description"] = $p->description_type_partieplante;
		}
		
		$idsForm = null;
		
		foreach($regionRowset as $r) {
			$typePlantes = null;
			foreach($typePlanteRowset as $t) {
				$parties = null;
				$idForm = $r->id_region."_".$t["id_type_plante"]."_".$t["id_fk_partieplante1_type_plante"];
				$parties[$t["id_fk_partieplante1_type_plante"]] = array (
						"nom" => $tabPartiePlante[$t["id_fk_partieplante1_type_plante"]]["nom"],
						"id_fk_partieplante" => $t["id_fk_partieplante1_type_plante"],
						"id_form" => $idForm,
						"nb_brut" => 0,
						"prix_unitaire_vente" => 0,
						"prix_unitaire_reprise" => 0,
				);
				$idsForm[] = $idForm;
					
				if ($t["id_fk_partieplante2_type_plante"] > 0) {
					$idForm = $r->id_region."_".$t["id_type_plante"]."_".$t["id_fk_partieplante2_type_plante"];
					$parties[$t["id_fk_partieplante2_type_plante"]] = array (
						"nom" => $tabPartiePlante[$t["id_fk_partieplante2_type_plante"]]["nom"],
						"id_fk_partieplante" => $t["id_fk_partieplante2_type_plante"],
						"id_form" => $idForm,
						"nb_brut" => 0,
						"prix_unitaire_vente" => 0,
						"prix_unitaire_reprise" => 0,
					);
					$idsForm[] = $idForm;
				}
				if ($t["id_fk_partieplante3_type_plante"] > 0) {
					$idForm = $r->id_region."_".$t["id_type_plante"]."_".$t["id_fk_partieplante3_type_plante"];
					$parties[$t["id_fk_partieplante3_type_plante"]] = array (
						"nom" => $tabPartiePlante[$t["id_fk_partieplante3_type_plante"]]["nom"],
						"id_fk_partieplante" => $t["id_fk_partieplante3_type_plante"],
						"id_form" => $idForm,
						"nb_brut" => 0,
						"prix_unitaire_vente" => 0,
						"prix_unitaire_reprise" => 0,
					);
					$idsForm[] = $idForm;
				}
				if ($t["id_fk_partieplante4_type_plante"] > 0) {
					$idForm = $r->id_region."_".$t["id_type_plante"]."_".$t["id_fk_partieplante4_type_plante"];
					$parties[$t["id_fk_partieplante4_type_plante"]] = array (
						"nom" => $tabPartiePlante[$t["id_fk_partieplante4_type_plante"]]["nom"],
						"id_fk_partieplante" => $t["id_fk_partieplante4_type_plante"],
						"id_form" => $idForm,
						"nb_brut" => 0,
						"prix_unitaire_vente" => 0,
						"prix_unitaire_reprise" => 0,
					);
					$idsForm[] = $idForm;
				}
				$typePlantes[$t["id_type_plante"]] = array("id_type_plante" => $t["id_type_plante"],
					"nom" => $t["nom_type_plante"],
					"categorie" => $t["categorie_type_plante"],
					"parties" => $parties,
				);
			}
			$regions[$r->id_region] = array(
					"id_region" => $r->id_region,
					"nom_region" => $r->nom_region,
					"type_plantes" => $typePlantes,
			);
		}
		
		$this->view->idsForm = $idsForm;
		$this->view->regions = $regions;
	}
}

