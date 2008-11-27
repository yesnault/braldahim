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
class Bral_Batchs_BoutiqueBois extends Bral_Batchs_Batch {
	
	public function calculBatchImpl() {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_BoutiqueBois - calculBatchImpl - enter -");
		
		Zend_Loader::loadClass('StockBois');
		
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
			$this->calculStockBois($r->id_region);
		}
		
		Bral_Util_Log::batchs()->trace("Bral_Batchs_BoutiqueBois - calculBatchImpl - exit -");
		return "Stock Bois cree pour le ".$mDate;
	}
	
	private function initDate() {
		$date = date("Y-m-d 0:0:0");
		$dateFin = date("Y-m-d 23:59:59");
		$this->dateDebut = Bral_Util_ConvertDate::get_date_add_day_to_date($date, -7);
		$this->dateFin = Bral_Util_ConvertDate::get_date_add_day_to_date($dateFin, -1);
		$this->dateDebutPrecedent = Bral_Util_ConvertDate::get_date_add_day_to_date($date, -14);
		$this->dateFinPrecedent = Bral_Util_ConvertDate::get_date_add_day_to_date($dateFin, -8);
		$this->moyenneNbJours = 7;
		
		Bral_Util_Log::batchs()->trace("Bral_Batchs_BoutiqueBois - initDate - dateDebut:".$this->dateDebut);
		Bral_Util_Log::batchs()->trace("Bral_Batchs_BoutiqueBois - initDate - dateFin(exclue):".$this->dateFin);
		Bral_Util_Log::batchs()->trace("Bral_Batchs_BoutiqueBois - initDate - dateDebutPrecedent:".$this->dateDebutPrecedent);
		Bral_Util_Log::batchs()->trace("Bral_Batchs_BoutiqueBois - initDate - dateFinPrecedent:".$this->dateFinPrecedent);
	}
	
	private function calculStockBois($idRegion) {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_BoutiqueBois - calculStockBois - enter - region:".$idRegion);
		
		/*
		 * Ma(s) : moyenne(nombre de vente depuis 7 jours -> J-1 à J-7)
		 * Mv(s) : moyenne(nombre d'achat depuis 7 jours)
		 * Ma(s-1) : moyenne(nombre de vente 7 jours précédent -> J-8 à J-14)
		 * Mv(s-1) : moyenne(nombre d'achat 7 jours précendent)
		 */
		
		Zend_Loader::loadClass('BoutiqueBois'); 
		
		$boutiqueBoisTable = new BoutiqueBois();
		
		$nombreAchat = $boutiqueBoisTable->countAchatByDateAndRegion($this->dateDebut, $this->dateFin, $idRegion);
		$nombreAchatPrecedent = $boutiqueBoisTable->countAchatByDateAndRegion($this->dateDebutPrecedent, $this->dateFinPrecedent, $idRegion);
		$nombreVente = $boutiqueBoisTable->countVenteByDateAndRegion($this->dateDebut, $this->dateFin, $idRegion);
		$nombreVentePrecedent = $boutiqueBoisTable->countVenteByDateAndRegion($this->dateDebutPrecedent, $this->dateFinPrecedent, $idRegion);
		
		$moyenneAchat = $nombreAchat / $this->moyenneNbJours;
		$moyenneAchatPrecedent = $nombreAchatPrecedent / $this->moyenneNbJours;
		$moyenneVente = $nombreVente / $this->moyenneNbJours;
		$moyenneVentePrecedent = $nombreVentePrecedent / $this->moyenneNbJours;
		
		Bral_Util_Log::batchs()->trace("Bral_Batchs_BoutiqueBois - calculStockBois - nombreAchat:".$nombreAchat." moyenneAchat:".$moyenneAchat);
		Bral_Util_Log::batchs()->trace("Bral_Batchs_BoutiqueBois - calculStockBois - nombreAchatPrecedent:".$nombreAchatPrecedent." moyenneAchatPrecedent:".$moyenneAchatPrecedent);
		Bral_Util_Log::batchs()->trace("Bral_Batchs_BoutiqueBois - calculStockBois - nombreVente:".$nombreVente." moyenneVente:".$moyenneVente);
		Bral_Util_Log::batchs()->trace("Bral_Batchs_BoutiqueBois - calculStockBois - nombreVentePrecedent:".$nombreVentePrecedent." moyenneVentePrecedent:".$moyenneVentePrecedent);
		
		$this->majStockBois($idRegion, $moyenneAchat, $moyenneAchatPrecedent, $moyenneVente, $moyenneVentePrecedent);
		
		Bral_Util_Log::batchs()->trace("Bral_Batchs_BoutiqueBois - calculStockBois - exit -");
	}
	
	/*
	 * Si :
	 * - c(s)=1 et quelque soit c(s-1) -> Prix d'achat reste le même, Prix de vente reste le même
	 * - c(s)<1 et [c(s-1)=1 ou c(s-1)>1] -> Prix d'achat augmente : arrsup(PrixAchat/c(s)), Prix de vente reste le même
	 * - c(s)<1 et c(s-1)<1  -> Prix d'achat augmente : arrsup(PrixAchat/c(s)), Prix de vente augmente : arrsup(PrixVente/c(s))
	 * - c(s)>1 et [c(s-1)=1 ou c(s-1)<1] -> Prix d'achat baisse : arrinf(PrixAchat/c(s))+1, Prix de vente reste le même
	 * - c(s)>1 et c(s-1)>1  -> Prix d'achat baisse : arrinf(PrixAchat/c(s))+1, Prix de vente baisse : arrinf(PrixVente/c(s))+1
	 */
	private function majStockBois($idRegion, $moyenneAchat, $moyenneAchatPrecedent, $moyenneVente, $moyenneVentePrecedent) {
		/*
		 * Ensuite on calcule les 2 ratios :
		 * c(s)=Mv(s)/Ma(s)
		 * c(s-1)=Mv(s-1)/Ma(s-1)
		 */
		if ($moyenneAchat > 0) {
			$ratio = $moyenneVente / $moyenneAchat;
		} else {
			$ratio = 1;
		}
		if ($moyenneAchatPrecedent > 0) {
			$ratioPrecedent = $moyenneVentePrecedent / $moyenneAchatPrecedent;
		} else {
			$ratioPrecedent = 1;
		}
		
		Bral_Util_Log::batchs()->trace("Bral_Batchs_BoutiqueBois - majStockBois - ratio:".$ratio. " ratioPrecedent:".$ratioPrecedent);
		
		$mDate = date("Y-m-d");
		
		$stockBoisTable = new StockBois();
		$stockBoisRowset = $stockBoisTable->findDernierStockByIdRegion($idRegion);
		
		foreach($stockBoisRowset as $s) {
			$nbInitial = $s["nb_rondin_initial_stock_bois"];
			$prixAchat = $s["prix_unitaire_vente_stock_bois"];
			$prixVente = $s["prix_unitaire_reprise_stock_bois"];
		
			if ($ratio == 1) {
				//Prix d'achat reste le même, Prix de vente reste le même
				Bral_Util_Log::batchs()->trace("Bral_Batchs_BoutiqueBois - majStockBois - Cas 1 - Prix d'achat reste le même, Prix de vente reste le même");
			} else if ($ratio < 1 && $ratioPrecedent >=1) {
				//Prix d'achat augmente : arrsup(PrixAchat/c(s)), Prix de vente reste le même
				Bral_Util_Log::batchs()->trace("Bral_Batchs_BoutiqueBois - majStockBois - Cas 2 - Prix d'achat augmente : arrsup(PrixAchat/c(s)), Prix de vente reste le même");
				$prixAchat = round($prixAchat/$ratio);
			} else if ($ratio < 1 && $ratioPrecedent <1) {
				//Prix d'achat augmente : arrsup(PrixAchat/c(s)), Prix de vente augmente : arrsup(PrixVente/c(s))
				Bral_Util_Log::batchs()->trace("Bral_Batchs_BoutiqueBois - majStockBois - Cas 3 - Prix d'achat augmente : arrsup(PrixAchat/c(s)), Prix de vente augmente : arrsup(PrixVente/c(s))");
				$prixAchat = round($prixAchat/$ratio);
				$prixVente = round($prixVente/$ratio);
			} else if ($ratio > 1 && $ratioPrecedent <=1) {
				//Prix d'achat baisse : arrinf(PrixAchat/c(s))+1, Prix de vente reste le même
				Bral_Util_Log::batchs()->trace("Bral_Batchs_BoutiqueBois - majStockBois - Cas 4 - Prix d'achat baisse : arrinf(PrixAchat/c(s))+1, Prix de vente reste le même");
				$prixAchat = floor($prixAchat/$ratio) + 1;
			} else if ($ratio > 1 && $ratioPrecedent >1) {
				//Prix d'achat baisse : arrinf(PrixAchat/c(s))+1, Prix de vente baisse : arrinf(PrixVente/c(s))+1
				Bral_Util_Log::batchs()->trace("Bral_Batchs_BoutiqueBois - majStockBois - Cas 5 - Prix d'achat baisse : arrinf(PrixAchat/c(s))+1, Prix de vente baisse : arrinf(PrixVente/c(s))+1");
				$prixAchat = floor($prixAchat/$ratio) + 1;
				$prixVente = floor($prixVente/$ratio) + 1;
			}
			
			$data = array(
				"date_stock_bois" => $mDate,
				"nb_rondin_initial_stock_bois" => $nbInitial,
				"nb_rondin_restant_stock_bois" => $nbInitial,
				"prix_unitaire_vente_stock_bois" => $prixAchat,
				"prix_unitaire_reprise_stock_bois" => $prixVente,
				"id_fk_region_stock_bois" => $idRegion,	
			);
			
			$stockBoisTable->insert($data);
		}
	}
}