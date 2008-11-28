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
class Bral_Batchs_BoutiquePlante extends Bral_Batchs_Boutique {
	
	public function calculBatchImpl() {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_BoutiquePlante - calculBatchImpl - enter -");
		
		Zend_Loader::loadClass('Region');
		Zend_Loader::loadClass('StockPartieplante');
		Zend_Loader::loadClass('BoutiquePartieplante');
		Zend_Loader::loadClass('TypePlante');
		
		$stockPartieplanteTable = new StockPartieplante();
		$mDate = date("Y-m-d");
		$stockPartieplanteRowset = $stockPartieplanteTable->findByDate($mDate);
		if (count($stockPartieplanteRowset) > 0) {
			Bral_Util_Log::batchs()->info("Bral_Batchs_BoutiquePlante - calculBatchImpl - Stock Bois deja present pour le ".$mDate);
			return "Stock Plante deja present pour le ".$mDate;
		}
		
		$typePlanteTable = new TypePlante();
		$regionTable = new Region();

		$typePlanteRowset = $typePlanteTable->fetchAllAvecEnvironnement();
		$regionRowset = $regionTable->fetchall();
		
		$this->initDate();
		
		foreach($regionRowset as $r) {
			foreach($typePlanteRowset as $t) {
				$this->calcul($r->id_region, $t["id_fk_partieplante1_type_plante"], $t["id_type_plante"]);
				if ($t["id_fk_partieplante2_type_plante"] > 0) {
					$this->calcul($r->id_region, $t["id_fk_partieplante2_type_plante"], $t["id_type_plante"]);
				}
				
				if ($t["id_fk_partieplante3_type_plante"] > 0) {
					$this->calcul($r->id_region, $t["id_fk_partieplante3_type_plante"], $t["id_type_plante"]);
				}
				
				if ($t["id_fk_partieplante4_type_plante"] > 0) {
					$this->calcul($r->id_region, $t["id_fk_partieplante4_type_plante"], $t["id_type_plante"]);
				}
			}
		}
		
		Bral_Util_Log::batchs()->trace("Bral_Batchs_BoutiquePlante - calculBatchImpl - exit -");
		return "Stock Plante cree pour le ".$mDate;
	}
	
	private function calcul($idRegion, $idTypePartiePlante, $idTypePlante) {
		$this->calculAchatVente($idRegion, $idTypePartiePlante, $idTypePlante);
		$this->calculMoyennes();
		$this->calculRatios();
		$this->calculStock($idRegion, $idTypePartiePlante, $idTypePlante);
	}
	
	public function calculAchatVente($idRegion, $idTypePartiePlante, $idTypePlante) {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_BoutiquePlante - calculAchatVente - enter - region:".$idRegion. " idTypePartiePlante:".$idTypePartiePlante." idTypePlante:".$idTypePlante);
		$boutiquePartieplanteTable = new BoutiquePartieplante();
		$this->nombreReprise = $boutiquePartieplanteTable->countRepriseByDateAndRegion($this->dateDebut, $this->dateFin, $idRegion, $idTypePartiePlante, $idTypePlante);
		$this->nombreReprisePrecedent = $boutiquePartieplanteTable->countRepriseByDateAndRegion($this->dateDebutPrecedent, $this->dateFinPrecedent, $idRegion, $idTypePartiePlante, $idTypePlante);
		$this->nombreVente = $boutiquePartieplanteTable->countVenteByDateAndRegion($this->dateDebut, $this->dateFin, $idRegion, $idTypePartiePlante, $idTypePlante);
		$this->nombreVentePrecedent = $boutiquePartieplanteTable->countVenteByDateAndRegion($this->dateDebutPrecedent, $this->dateFinPrecedent, $idRegion, $idTypePartiePlante, $idTypePlante);
		Bral_Util_Log::batchs()->trace("Bral_Batchs_BoutiquePlante - calculAchatVente - exit -");
	}
	
	public function calculStock($idRegion, $idTypePartiePlante, $idTypePlante) {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_BoutiquePlante - calculStock - ratio:".$this->ratio. " ratioPrecedent:".$this->ratioPrecedent);
		
		$stockMineraiTable = new StockPartieplante();
		$stockMineraiRowset = $stockMineraiTable->findDernierStockByIdRegion($idRegion, $idTypePartiePlante, $idTypePlante);
		
		foreach($stockMineraiRowset as $s) {
			$nbInitial = $s["nb_brut_initial_stock_partieplante"];
			$tabPrix["prixReprise"] = $s["prix_unitaire_reprise_stock_partieplante"];
			$tabPrix["prixVente"] = $s["prix_unitaire_vente_stock_partieplante"];
			$tabPrix = $this->calculPrix($tabPrix);
			$this->updateStockBase($idRegion, $idTypePartiePlante, $idTypePlante, $nbInitial, $tabPrix);
		}
	}
	
	public function updateStockBase($idRegion, $idTypePartiePlante, $idTypePlante, $nbInitial, $tabPrix) {
		$mDate = date("Y-m-d");
		
		$data = array(
			"date_stock_partieplante" => $mDate,
			"id_fk_type_stock_partieplante" => $idTypePartiePlante,
			"id_fk_type_plante_stock_partieplante" => $idTypePlante,
			"nb_brut_initial_stock_partieplante" => $nbInitial,
			"nb_brut_restant_stock_partieplante" => $nbInitial,
			"prix_unitaire_vente_stock_partieplante" => $tabPrix["prixVente"],
			"prix_unitaire_reprise_stock_partieplante" => $tabPrix["prixReprise"],
			"id_fk_region_stock_partieplante" => $idRegion,	
		);
		$stockMineraiTable = new StockPartieplante();
		$stockMineraiTable->insert($data);
	}
}