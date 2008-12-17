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
class Bral_Batchs_BoutiqueTabac extends Bral_Batchs_Boutique {
	
	public function calculBatchImpl() {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_BoutiqueTabac - calculBatchImpl - enter -");
		
		Zend_Loader::loadClass('StockTabac');
		Zend_Loader::loadClass('TypeTabac');
		Zend_Loader::loadClass('BoutiqueTabac');
		Zend_Loader::loadClass('Region');
		
		$stockTabacTable = new StockTabac();
		$mDate = date("Y-m-d");
		$stockTabacRowset = $stockTabacTable->findByDate($mDate);
		if (count($stockTabacRowset) > 0) {
			Bral_Util_Log::batchs()->info("Bral_Batchs_BoutiqueTabac - calculBatchImpl - Stock Tabac deja present pour le ".$mDate);
			return "Stock Tabac deja present pour le ".$mDate;
		}
		
		$regionTable = new Region();
		$typeTabacTable = new TypeTabac();
		
		$typeTabacRowset = $typeTabacTable->fetchall();
		$typeTabacRowset = $typeTabacRowset->toArray();
		$regionRowset = $regionTable->fetchall();
		
		$this->initDate();
		
		foreach($regionRowset as $r) {
			foreach($typeTabacRowset as $t) {
				$this->calculMoyennes();
				$this->calculRatios();
				$this->calculStock($r->id_region, $t["id_type_tabac"]);
			}
		}
		
		Bral_Util_Log::batchs()->trace("Bral_Batchs_BoutiqueTabac - calculBatchImpl - exit -");
		return "Stock Tabac cree pour le ".$mDate;
	}
	
	public function calculAchatVente($idRegion, $idTypeTabac) {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_BoutiqueTabac - calculAchatVente - enter - region:".$idRegion." idTabac:".$idTypeTabac);
		$boutiqueTabacTable = new BoutiqueTabac();
		$this->nombreReprise = $boutiqueTabacTable->countRepriseByDateAndRegion($this->dateDebut, $this->dateFin, $idRegion, $idTypeTabac);
		$this->nombreReprisePrecedent = $boutiqueTabacTable->countRepriseByDateAndRegion($this->dateDebutPrecedent, $this->dateFinPrecedent, $idRegion, $idTypeTabac);
		$this->nombreVente = $boutiqueTabacTable->countVenteByDateAndRegion($this->dateDebut, $this->dateFin, $idRegion, $idTypeTabac);
		$this->nombreVentePrecedent = $boutiqueTabacTable->countVenteByDateAndRegion($this->dateDebutPrecedent, $this->dateFinPrecedent, $idRegion, $idTypeTabac);
		Bral_Util_Log::batchs()->trace("Bral_Batchs_BoutiqueTabac - calculAchatVente - exit -");
	}
	
	
	public function calculStock($idRegion, $idTypeTabac) {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_BoutiqueTabac - calculStock - ratio:".$this->ratio. " ratioPrecedent:".$this->ratioPrecedent);
		
		$stockTabacTable = new StockTabac();
		$stockTabacRowset = $stockTabacTable->findDernierStockByIdRegion($idRegion, $idTypeTabac);
		
		foreach($stockTabacRowset as $s) {
			$nbInitial = $s["nb_feuille_initial_stock_tabac"];
			$tabPrix["prixReprise"] = $s["prix_unitaire_reprise_stock_tabac"];
			$tabPrix["prixVente"] = $s["prix_unitaire_vente_stock_tabac"];
			$tabPrix = $this->calculPrix($tabPrix);
			$this->updateStockBase($idRegion, $idTypeTabac, $nbInitial, $tabPrix);
		}
	}
	
	public function updateStockBase($idRegion, $idTypeTabac, $nbInitial, $tabPrix) {
		$mDate = date("Y-m-d");
		
		$data = array(
			"date_stock_tabac" => $mDate,
			"id_fk_type_stock_tabac" => $idTypeTabac,
			"nb_feuille_initial_stock_tabac" => $nbInitial,
			"nb_feuille_restant_stock_tabac" => $nbInitial,
			"prix_unitaire_vente_stock_tabac" => $tabPrix["prixVente"],
			"prix_unitaire_reprise_stock_tabac" => $tabPrix["prixReprise"],
			"id_fk_region_stock_tabac" => $idRegion,	
		);
		$stockTabacTable = new StockTabac();
		$stockTabacTable->insert($data);
	}
}