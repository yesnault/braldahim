<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: StockTabac.php 774 2008-12-17 22:03:24Z yvonnickesnault $
 * $Author: yvonnickesnault $
 * $LastChangedDate: 2008-12-17 23:03:24 +0100 (Wed, 17 Dec 2008) $
 * $LastChangedRevision: 774 $
 * $LastChangedBy: yvonnickesnault $
 */
class StockTabac extends Zend_Db_Table {
	protected $_name = 'stock_tabac';
	protected $_primary = array('id_stock_tabac');

	function findDernierStock() {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('stock_tabac', array('max(date_stock_tabac) as date_stock_tabac'))
		->where('date_stock_tabac <= ?', date("Y-m-d 23:59:59"));
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		if (count($resultat) != 1) {
			throw new Zend_Exception("StockTabac::findDernierStockByIdRegion count invalide:".count($resultat). " idregion:".$idRegion);
		}
		
		$select = $db->select();
		$select->from('stock_tabac', '*')
		->from('region')
		->from('type_tabac')
		->where('id_fk_region_stock_tabac  = id_region')
		->where('id_fk_type_stock_tabac = id_type_tabac')
		->where('date_stock_tabac = ?', $resultat[0]["date_stock_tabac"])
		->order('nom_type_tabac ASC');
		
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
	function findByDate($mDate) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('stock_tabac', '*')
		->from('type_tabac', 'nom_type_tabac')
		->from('region', 'nom_region')
		->where('stock_tabac.id_fk_type_stock_tabac = type_tabac.id_type_tabac')
		->where('region.id_region = stock_tabac.id_fk_region_stock_tabac')
		->where('date_stock_tabac  = ?', $mDate)	
		->order(array('id_fk_region_stock_tabac', 'id_fk_type_stock_tabac'));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
	function findDistinctDate() {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('stock_tabac', 'distinct(date_stock_tabac) as date_stock_tabac')
		->order(array('date_stock_tabac DESC'));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}
