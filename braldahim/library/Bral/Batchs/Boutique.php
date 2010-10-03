<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
abstract class Bral_Batchs_Boutique extends Bral_Batchs_Batch {
	
	/*
	 * Init des dates utilisees pour les moyennes
	 * Ma(s) : moyenne(nombre de vente depuis 7 jours -> J-1 à J-7)
	 * Mv(s) : moyenne(nombre de reprise depuis 7 jours)
	 * Ma(s-1) : moyenne(nombre de vente 7 jours précédent -> J-8 à J-14)
	 * Mv(s-1) : moyenne(nombre de reprise 7 jours précendent)
	 */
	protected function initDate() {
		$date = date("Y-m-d 0:0:0");
		$dateFin = date("Y-m-d 23:59:59");
		$this->dateDebut = Bral_Util_ConvertDate::get_date_add_day_to_date($date, -7);
		$this->dateFin = Bral_Util_ConvertDate::get_date_add_day_to_date($dateFin, -1);
		$this->dateDebutPrecedent = Bral_Util_ConvertDate::get_date_add_day_to_date($date, -14);
		$this->dateFinPrecedent = Bral_Util_ConvertDate::get_date_add_day_to_date($dateFin, -8);
		$this->moyenneNbJours = 7;
		
		Bral_Util_Log::batchs()->trace("Bral_Batchs_BoutiqueMinerai - initDate - dateDebut:".$this->dateDebut);
		Bral_Util_Log::batchs()->trace("Bral_Batchs_BoutiqueMinerai - initDate - dateFin(exclue):".$this->dateFin);
		Bral_Util_Log::batchs()->trace("Bral_Batchs_BoutiqueMinerai - initDate - dateDebutPrecedent:".$this->dateDebutPrecedent);
		Bral_Util_Log::batchs()->trace("Bral_Batchs_BoutiqueMinerai - initDate - dateFinPrecedent:".$this->dateFinPrecedent);
	}
	
	/*
	 * Ma(s) : moyenne(nombre de vente depuis 7 jours -> J-1 à J-7)
	 * Mv(s) : moyenne(nombre d'reprise depuis 7 jours)
	 * Ma(s-1) : moyenne(nombre de vente 7 jours précédent -> J-8 à J-14)
	 * Mv(s-1) : moyenne(nombre d'reprise 7 jours précendent)
	 */
	protected function calculMoyennes() {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Boutique - calculMoyennes - enter -");
		$this->moyenneReprise = $this->nombreReprise / $this->moyenneNbJours;
		$this->moyenneReprisePrecedent = $this->nombreReprisePrecedent / $this->moyenneNbJours;
		$this->moyenneVente = $this->nombreVente / $this->moyenneNbJours;
		$this->moyenneVentePrecedent = $this->nombreVentePrecedent / $this->moyenneNbJours;
		
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Boutique - calculMoyennes - nombreReprise:".$this->nombreReprise." moyenneReprise:".$this->moyenneReprise);
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Boutique - calculMoyennes - nombreReprisePrecedent:".$this->nombreReprisePrecedent." moyenneReprisePrecedent:".$this->moyenneReprisePrecedent);
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Boutique - calculMoyennes - nombreVente:".$this->nombreVente." moyenneVente:".$this->moyenneVente);
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Boutique - calculMoyennes - nombreVentePrecedent:".$this->nombreVentePrecedent." moyenneVentePrecedent:".$this->moyenneVentePrecedent);
		
		$this->calculRatios();
		
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Boutique - calculMoyennes - exit -");
	}
	
	/*
	 * Ensuite on calcule les 2 ratios :
	 * c(s)=Mv(s)/Ma(s)
	 * c(s-1)=Mv(s-1)/Ma(s-1)
	 */
	protected function calculRatios() {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Boutique - calculRatios - enter -");
		if ($this->moyenneReprise > 0 && $this->moyenneVente > 0) {
			$this->ratio = $this->moyenneReprise / $this->moyenneVente;
		} else {
			$this->ratio = 1;
		}
		if ($this->moyenneReprisePrecedent > 0 && $this->moyenneVentePrecedent > 0) {
			$this->ratioPrecedent = $this->moyenneReprisePrecedent / $this->moyenneVentePrecedent;
		} else {
			$this->ratioPrecedent = 1;
		}
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Boutique - ratio=".$this->ratio." ratioPrecedent=".$this->ratioPrecedent." - exit -");
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Boutique - calculRatios - exit -");
	}
	
	protected function calculPrix($tabPrix) {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Boutique - calculPrix - enter -");
		if ($this->ratio == 1) {
			//Prix d'reprise reste le même, Prix de vente reste le même
			Bral_Util_Log::batchs()->trace("Bral_Batchs_Boutique - majStockBois - Cas 1 - Prix d'reprise reste le même, Prix de vente reste le même");
		} else if ($this->ratio < 1 && $this->ratioPrecedent >=1) {
			//Prix d'reprise augmente : arrsup(PrixReprise/c(s)), Prix de vente reste le même
			Bral_Util_Log::batchs()->trace("Bral_Batchs_Boutique - majStockBois - Cas 2 - Prix d'reprise augmente : arrsup(PrixReprise/c(s)), Prix de vente reste le même");
			$tabPrix["prixReprise"] = round($tabPrix["prixReprise"]/$this->ratio);
		} else if ($this->ratio < 1 && $this->ratioPrecedent <1) {
			//Prix d'reprise augmente : arrsup(PrixReprise/c(s)), Prix de vente augmente : arrsup(PrixVente/c(s))
			Bral_Util_Log::batchs()->trace("Bral_Batchs_Boutique - majStockBois - Cas 3 - Prix d'reprise augmente : arrsup(PrixReprise/c(s)), Prix de vente augmente : arrsup(PrixVente/c(s))");
			$tabPrix["prixReprise"] = round($tabPrix["prixReprise"]/$this->ratio);
			$tabPrix["prixVente"] = round($tabPrix["prixVente"]/$this->ratio);
		} else if ($this->ratio > 1 && $this->ratioPrecedent <=1) {
			//Prix d'reprise baisse : arrinf(PrixReprise/c(s))+1, Prix de vente reste le même
			Bral_Util_Log::batchs()->trace("Bral_Batchs_Boutique - majStockBois - Cas 4 - Prix d'reprise baisse : arrinf(PrixReprise/c(s))+1, Prix de vente reste le même");
			$tabPrix["prixReprise"] = floor($tabPrix["prixReprise"]/$this->ratio) + 1;
		} else if ($this->ratio > 1 && $this->ratioPrecedent >1) {
			//Prix d'reprise baisse : arrinf(PrixReprise/c(s))+1, Prix de vente baisse : arrinf(PrixVente/c(s))+1
			Bral_Util_Log::batchs()->trace("Bral_Batchs_Boutique - majStockBois - Cas 5 - Prix d'reprise baisse : arrinf(PrixReprise/c(s))+1, Prix de vente baisse : arrinf(PrixVente/c(s))+1");
			$tabPrix["prixReprise"] = floor($tabPrix["prixReprise"]/$this->ratio) + 1;
			$tabPrix["prixVente"] = floor($tabPrix["prixVente"]/$this->ratio) + 1;
		}
		
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Boutique - majStockBois -Prix d'reprise:".$tabPrix["prixReprise"]. " Prix de vente:".$tabPrix["prixVente"]);
		
		if ($tabPrix["prixReprise"] > $tabPrix["prixVente"] / 2) {
			$tabPrix["prixReprise"] = floor($tabPrix["prixVente"] / 2);
			Bral_Util_Log::batchs()->trace("Bral_Batchs_Boutique - majStockBois - Ajustement. reprise=vente/2. Prix d'reprise:".$tabPrix["prixReprise"]. " Prix de vente:".$tabPrix["prixVente"]);
		}
		
		
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Boutique - calculPrix - exit -");
		return $tabPrix;
	}
}