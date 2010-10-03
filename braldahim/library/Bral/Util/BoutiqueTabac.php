<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Util_BoutiqueTabac {
	
	public static function construireTabPrix($estFormulaire, $idRegion) {
		Zend_Loader::loadClass('TypeTabac');
		
		$typeTabacTable = new TypeTabac();
		$typeTabacRowset = $typeTabacTable->fetchAll();
		
		Zend_Loader::loadClass('StockTabac');
		$stockTabacTable = new StockTabac();
		$stockTabacRowset = $stockTabacTable->findDernierStockByIdRegion($idRegion);
		
		if ($stockTabacRowset == null) {
			return null;
		}
		
		$numChamp = 0;
		
		foreach ($typeTabacRowset as $t) {
			$prixUnitaireVente = "Prix inconnu";
			$prixUnitaireReprise = "Prix inconnu";
			
			foreach ($stockTabacRowset as $s) {
				if ($s["id_fk_type_stock_tabac"] == $t->id_type_tabac) {
					$prixUnitaireVente = $s["prix_unitaire_vente_stock_tabac"];
					$prixUnitaireReprise = $s["prix_unitaire_reprise_stock_tabac"];
					$nbStockInitial = $s["nb_feuille_initial_stock_tabac"];
					$nbStockRestant = $s["nb_feuille_restant_stock_tabac"];
					$dateStock = $s["date_stock_tabac"];
					$idStock = $s["id_stock_tabac"];
					break;
				}
			}
			
			$numChamp++;
			$idChamp = "valeur_".$numChamp;
					
			$tabBrut = array(
				"id_type_tabac" => $t->id_type_tabac, 
				"nom_systeme" => $t->nom_systeme_type_tabac, 
				"description" => $t->description_type_tabac,
				"idStock" => $idStock,
				"dateStock" => $dateStock,
				"prixUnitaireVente" => $prixUnitaireVente,
				"prixUnitaireReprise" => $prixUnitaireReprise,
				"nbStockInitial" => $nbStockInitial,
				"nbStockRestant" => $nbStockRestant,
				"type" => $t->nom_type_tabac,
			);
			
			if ($estFormulaire) {
				$tabBrut["id_champ"] = $idChamp;
			}
			
			$tabac[] = $tabBrut;
		}
		return $tabac;
	}
}