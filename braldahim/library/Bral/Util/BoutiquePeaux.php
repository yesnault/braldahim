<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: $
 * $Author: $
 * $LastChangedDate: $
 * $LastChangedRevision: $
 * $LastChangedBy: $
 */
class Bral_Util_BoutiquePeaux {
	
	public static function construireTabStockPrix($idRegion) {
		Zend_Loader::loadClass('StockPeau');
		
		$stockPeauTable = new StockPeau();
		$stockPeauxRowset = $stockPeauTable->findDernierStockByIdRegion($idRegion);
		$tabPrix = null;
		
		if (count($stockPeauxRowset) != 1) {
			throw new Zend_Exception(get_class($this)."::construireTabStockPrix count(stockPeauxRowset) != 1 :".count($stockPeauxRowset));
		}
		
		$s = $stockPeauxRowset[0];
		$tabPrix[] = array(
			'id_stock_peau' => $s["id_stock_peau"],
			'date_stock_peau' => $s["date_stock_peau"],
			'nb_peau_initial_stock_peau' => $s["nb_peau_initial_stock_peau"],
			'nb_peau_restant_stock_peau' => $s["nb_peau_restant_stock_peau"],
			'prix_unitaire_vente_stock_peau' => $s["prix_unitaire_vente_stock_peau"],
			'prix_unitaire_reprise_stock_peau' => $s["prix_unitaire_reprise_stock_peau"],
			'nb_peau_initial_stock_peau' => $s["nb_peau_initial_stock_peau"],
			'nb_peau_restant_stock_peau' => $s["nb_peau_restant_stock_peau"],
		);
		
		return $tabPrix;
	}
}