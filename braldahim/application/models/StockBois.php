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
class StockBois extends Zend_Db_Table {
	protected $_name = 'stock_bois';
	protected $_primary = array('id_stock_bois');
	
	function findDernierStockByIdRegion($idRegion) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('stock_bois', array('max(date_stock_bois) as date_stock_bois', 'nb_rondin_initial_stock_bois', 'nb_rondin_restant_stock_bois', 'prix_unitaire_vente_stock_bois', 'prix_unitaire_reprise_stock_bois'))
		->where('id_fk_region_stock_bois  = ?', $idRegion)
		->group(array('nb_rondin_initial_stock_bois', 'nb_rondin_restant_stock_bois', 'prix_unitaire_vente_stock_bois', 'prix_unitaire_reprise_stock_bois'));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
	function findByDate($mDate) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('stock_bois', '*')
		->from('region', 'nom_region')
		->where('region.id_region = stock_bois.id_fk_region_stock_bois')
		->where('date_stock_bois  = ?', $mDate)	
		->order(array('id_fk_region_stock_bois'));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
	function findDistinctDate() {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('stock_bois', 'distinct(date_stock_bois) as date_stock_bois')
		->order(array('date_stock_bois DESC'));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
}
