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
class StockPeau extends Zend_Db_Table {
	protected $_name = 'stock_peau';
	protected $_primary = array('id_stock_peau');
	
	public function findDernierStock() {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('stock_peau', array('max(date_stock_peau) as date_stock_peau'))
		->where('date_stock_peau <= ?', date("Y-m-d 23:59:59"));
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		if (count($resultat) != 1) {
			throw new Zend_Exception("StockPeau::findDernierStockByIdRegion count invalide:".count($resultat). " idregion:".$idRegion);
		}
		
		$select = $db->select();
		$select->from('stock_peau', '*')
		->from('region')
		->where('id_fk_region_stock_peau = id_region')
		->where('date_stock_peau = ?', $resultat[0]["date_stock_peau"])
		->order('nom_region ASC');
		
		$sql = $select->__toString();
		
		return $db->fetchAll($sql);
	}
	
	public function findByDate($mDate) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('stock_peau', '*')
		->from('region', 'nom_region')
		->where('region.id_region = stock_peau.id_fk_region_stock_peau')
		->where('date_stock_peau  = ?', $mDate)	
		->order(array('id_fk_region_stock_peau'));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
	public function findDistinctDate() {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('stock_peau', 'distinct(date_stock_peau) as date_stock_peau')
		->order(array('date_stock_peau DESC'));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}
