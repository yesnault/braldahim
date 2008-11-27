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
class Bral_Batchs_BoutiqueBois extends Bral_Batchs_Boutique {
	
	public function calculBatchImpl() {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_BoutiqueBois - calculBatchImpl - enter -");
		
		Zend_Loader::loadClass('StockBois');
		Zend_Loader::loadClass('BoutiqueBois'); 
		
		$stockBoisTable = new StockBois();
		$mDate = date("Y-m-d");
		$stockBoisRowset = $stockBoisTable->findByDate($mDate);
		if (count($stockBoisRowset) > 0) {
			Bral_Util_Log::batchs()->info("Bral_Batchs_BoutiqueBois - calculBatchImpl - Stock Bois deja present pour le ".$mDate);
			return "Stock Bois deja present pour le ".$mDate;
		}
		
		Zend_Loader::loadClass('Region');
		$regionTable = new Region();
		$regionRowset = $regionTable->fetchall();
		
		$this->initDate();
		
		foreach($regionRowset as $r) {
			$this->calculAchatVente($r->id_region);
			$this->calculMoyennes();
			$this->calculRatios();
			$this->calculStock($r->id_region);
		}
		
		Bral_Util_Log::batchs()->trace("Bral_Batchs_BoutiqueBois - calculBatchImpl - exit -");
		return "Stock Bois cree pour le ".$mDate;
	}
	
	public function calculAchatVente($idRegion) {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_BoutiqueBois - calculAchatVente - enter - region:".$idRegion);
		$boutiqueBoisTable = new BoutiqueBois();
		$this->nombreAchat = $boutiqueBoisTable->countAchatByDateAndRegion($this->dateDebut, $this->dateFin, $idRegion);
		$this->nombreAchatPrecedent = $boutiqueBoisTable->countAchatByDateAndRegion($this->dateDebutPrecedent, $this->dateFinPrecedent, $idRegion);
		$this->nombreVente = $boutiqueBoisTable->countVenteByDateAndRegion($this->dateDebut, $this->dateFin, $idRegion);
		$this->nombreVentePrecedent = $boutiqueBoisTable->countVenteByDateAndRegion($this->dateDebutPrecedent, $this->dateFinPrecedent, $idRegion);
		Bral_Util_Log::batchs()->trace("Bral_Batchs_BoutiqueBois - calculAchatVente - exit -");
	}
	
	
	/*
	 * Si :
	 * - c(s)=1 et quelque soit c(s-1) -> Prix d'achat reste le même, Prix de vente reste le même
	 * - c(s)<1 et [c(s-1)=1 ou c(s-1)>1] -> Prix d'achat augmente : arrsup(PrixAchat/c(s)), Prix de vente reste le même
	 * - c(s)<1 et c(s-1)<1  -> Prix d'achat augmente : arrsup(PrixAchat/c(s)), Prix de vente augmente : arrsup(PrixVente/c(s))
	 * - c(s)>1 et [c(s-1)=1 ou c(s-1)<1] -> Prix d'achat baisse : arrinf(PrixAchat/c(s))+1, Prix de vente reste le même
	 * - c(s)>1 et c(s-1)>1  -> Prix d'achat baisse : arrinf(PrixAchat/c(s))+1, Prix de vente baisse : arrinf(PrixVente/c(s))+1
	 */
	public function calculStock($idRegion) {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_BoutiqueBois - calculStock - ratio:".$this->ratio. " ratioPrecedent:".$this->ratioPrecedent);
		
		$stockBoisTable = new StockBois();
		$stockBoisRowset = $stockBoisTable->findDernierStockByIdRegion($idRegion);
		
		foreach($stockBoisRowset as $s) {
			$nbInitial = $s["nb_rondin_initial_stock_bois"];
			$tabPrix["prixAchat"] = $s["prix_unitaire_vente_stock_bois"];
			$tabPrix["prixVente"] = $s["prix_unitaire_reprise_stock_bois"];
			$tabPrix = $this->calculPrix($tabPrix);
			$this->updateStockBase($idRegion, $nbInitial, $tabPrix);
		}
	}
	
	public function updateStockBase($idRegion, $nbInitial, $tabPrix) {
		$mDate = date("Y-m-d");
		
		$data = array(
			"date_stock_bois" => $mDate,
			"nb_rondin_initial_stock_bois" => $nbInitial,
			"nb_rondin_restant_stock_bois" => $nbInitial,
			"prix_unitaire_vente_stock_bois" => $tabPrix["prixAchat"],
			"prix_unitaire_reprise_stock_bois" => $tabPrix["prixVente"],
			"id_fk_region_stock_bois" => $idRegion,	
		);
		$stockBoisTable = new StockBois();
		$stockBoisTable->insert($data);
	}
}