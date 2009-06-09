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
class Bral_Batchs_BoutiqueMinerai extends Bral_Batchs_Boutique {

	public function calculBatchImpl() {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_BoutiqueMinerai - calculBatchImpl - enter -");

		Zend_Loader::loadClass('StockMinerai');
		Zend_Loader::loadClass('TypeMinerai');
		Zend_Loader::loadClass('BoutiqueMinerai');
		Zend_Loader::loadClass('Region');

		$stockMineraiTable = new StockMinerai();
		$mDate = date("Y-m-d");
		$stockMineraiRowset = $stockMineraiTable->findByDate($mDate);
		if (count($stockMineraiRowset) > 0) {
			Bral_Util_Log::batchs()->info("Bral_Batchs_BoutiqueMinerai - calculBatchImpl - Stock Minerai deja present pour le ".$mDate);
			return "Stock Minerai deja present pour le ".$mDate;
		}

		$regionTable = new Region();
		$typeMineraiTable = new TypeMinerai();

		$typeMineraiRowset = $typeMineraiTable->fetchall();
		$typeMineraiRowset = $typeMineraiRowset->toArray();
		$regionRowset = $regionTable->fetchall();

		$this->initDate();


		foreach($typeMineraiRowset as $t) {
			$this->calculAchatVente($t["id_type_minerai"]);
			$this->calculMoyennes();
			$this->calculRatios();

			foreach($regionRowset as $r) {
				$this->calculStock($r->id_region, $t["id_type_minerai"]);
			}
		}

		Bral_Util_Log::batchs()->trace("Bral_Batchs_BoutiqueMinerai - calculBatchImpl - exit -");
		return "Stock Minerai cree pour le ".$mDate;
	}

	public function calculAchatVente($idTypeMinerai) {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_BoutiqueMinerai - calculAchatVente - enter - idMinerai:".$idTypeMinerai);
		$boutiqueMineraiTable = new BoutiqueMinerai();
		$this->nombreReprise = $boutiqueMineraiTable->countRepriseByDate($this->dateDebut, $this->dateFin, $idTypeMinerai);
		$this->nombreReprisePrecedent = $boutiqueMineraiTable->countRepriseByDate($this->dateDebutPrecedent, $this->dateFinPrecedent, $idTypeMinerai);
		$this->nombreVente = $boutiqueMineraiTable->countVenteByDate($this->dateDebut, $this->dateFin, $idTypeMinerai);
		$this->nombreVentePrecedent = $boutiqueMineraiTable->countVenteByDate($this->dateDebutPrecedent, $this->dateFinPrecedent, $idTypeMinerai);
		Bral_Util_Log::batchs()->trace("Bral_Batchs_BoutiqueMinerai - calculAchatVente - exit -");
	}


	public function calculStock($idRegion, $idTypeMinerai) {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_BoutiqueMinerai - calculStock - ratio:".$this->ratio. " ratioPrecedent:".$this->ratioPrecedent);

		$stockMineraiTable = new StockMinerai();
		$stockMineraiRowset = $stockMineraiTable->findDernierStockByIdRegion($idRegion, $idTypeMinerai);

		foreach($stockMineraiRowset as $s) {
			$nbInitial = $s["nb_brut_initial_stock_minerai"];
			$tabPrix["prixReprise"] = $s["prix_unitaire_reprise_stock_minerai"];
			$tabPrix["prixVente"] = $s["prix_unitaire_vente_stock_minerai"];
			$tabPrix = $this->calculPrix($tabPrix);
			$this->updateStockBase($idRegion, $idTypeMinerai, $nbInitial, $tabPrix);
		}
		Bral_Util_Log::batchs()->trace("Bral_Batchs_BoutiqueMinerai - calculStock - exit");
	}

	public function updateStockBase($idRegion, $idTypeMinerai, $nbInitial, $tabPrix) {
		$mDate = date("Y-m-d");

		$data = array(
			"date_stock_minerai" => $mDate,
			"id_fk_type_stock_minerai" => $idTypeMinerai,
			"nb_brut_initial_stock_minerai" => $nbInitial,
			"nb_brut_restant_stock_minerai" => $nbInitial,
			"prix_unitaire_vente_stock_minerai" => $tabPrix["prixVente"],
			"prix_unitaire_reprise_stock_minerai" => $tabPrix["prixReprise"],
			"id_fk_region_stock_minerai" => $idRegion,	
		);
		$stockMineraiTable = new StockMinerai();
		$stockMineraiTable->insert($data);
	}
}