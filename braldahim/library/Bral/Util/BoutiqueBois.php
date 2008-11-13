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
class Bral_Util_BoutiqueBois {
	
	public static function construireTabStockPrix($idRegion) {
		Zend_Loader::loadClass('StockBois');
		
		$stockBoisTable = new StockBois();
		$stockBoisRowset = $stockBoisTable->findDernierStockByIdRegion($idRegion);
		$tabPrix = null;
		foreach($stockBoisRowset as $s) {
			$tabPrix[] = array(
				'date_stock_bois' => $s["date_stock_bois"],
				'nb_rondin_initial_stock_bois' => $s["nb_rondin_initial_stock_bois"],
				'nb_rondin_restant_stock_bois' => $s["nb_rondin_restant_stock_bois"],
				'prix_unitaire_vente_stock_bois' => $s["prix_unitaire_vente_stock_bois"],
				'prix_unitaire_reprise_stock_bois' => $s["prix_unitaire_reprise_stock_bois"],
			);
		}
		
		return $tabPrix;
	}
	
	public static function calculAndUpdatePrixUnitaires() {
		
	}
}