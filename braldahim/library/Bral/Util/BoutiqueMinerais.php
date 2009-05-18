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
class Bral_Util_BoutiqueMinerais {
	
	public static function construireTabPrix($estFormulaire, $idRegion) {
		Zend_Loader::loadClass('TypeMinerai');
		
		$typeMineraiTable = new TypeMinerai();
		$typeMineraiRowset = $typeMineraiTable->fetchAll();
		
		Zend_Loader::loadClass('StockMinerai');
		$stockMineraiTable = new StockMinerai();
		$stockMineraiRowset = $stockMineraiTable->findDernierStockByIdRegion($idRegion);
		
		if ($stockMineraiRowset == null) {
			return null;
		}
		
		$numChamp = 1;
		
		foreach ($typeMineraiRowset as $t) {
			$prixUnitaireVente = "Prix inconnu";
			$prixUnitaireReprise = "Prix inconnu";
			
			foreach ($stockMineraiRowset as $s) {
				if ($s["id_fk_type_stock_minerai"] == $t->id_type_minerai) {
					$prixUnitaireVente = $s["prix_unitaire_vente_stock_minerai"];
					$prixUnitaireReprise = $s["prix_unitaire_reprise_stock_minerai"];
					$nbStockInitial = $s["nb_brut_initial_stock_minerai"];
					$nbStockRestant = $s["nb_brut_restant_stock_minerai"];
					$dateStock = $s["date_stock_minerai"];
					$idStock = $s["id_stock_minerai"];
					break;
				}
			}
			
			$numChamp++;
			$idChamp = "valeur_".$numChamp;
					
			$tabBrut = array(
				"id_type_minerai" => $t->id_type_minerai, 
				"estLingot" => false,
				"nom_systeme" => $t->nom_systeme_type_minerai, 
				"description" => $t->description_type_minerai,
				"idStock" => $idStock,
				"dateStock" => $dateStock,
				"prixUnitaireVente" => $prixUnitaireVente,
				"prixUnitaireReprise" => $prixUnitaireReprise,
				"nbStockInitial" => $nbStockInitial,
				"nbStockRestant" => $nbStockRestant,
				"type" => $t->nom_type_minerai,
			);
			
			if ($estFormulaire) {
				$tabBrut["id_champ"] = $idChamp;
			}
			
			$minerais[] = $tabBrut;
		}
		return $minerais;
	}
}