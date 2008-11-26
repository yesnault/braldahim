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
class StockMinerai extends Zend_Db_Table {
	protected $_name = 'stock_minerai';
	protected $_primary = array('id_stock_minerai');

	function findDernierStockByIdRegion($idRegion) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('stock_minerai', array('max(date_stock_minerai) as date_stock_minerai', 'id_fk_type_stock_minerai', 'nb_brut_initial_stock_minerai', 'nb_brut_restant_stock_minerai', 'prix_unitaire_vente_stock_minerai', 'prix_unitaire_reprise_stock_minerai'))
		->where('id_fk_region_stock_minerai  = ?', $idRegion)
		->group(array('id_fk_type_stock_minerai', 'nb_brut_initial_stock_minerai', 'nb_brut_restant_stock_minerai', 'prix_unitaire_vente_stock_minerai', 'prix_unitaire_reprise_stock_minerai'));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
	function findByDate($mDate) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('stock_minerai', '*')
		->from('type_minerai', 'nom_type_minerai')
		->from('region', 'nom_region')
		->where('stock_minerai.id_fk_type_stock_minerai = type_minerai.id_type_minerai')
		->where('region.id_region = stock_minerai.id_fk_region_stock_minerai')
		->where('date_stock_minerai  = ?', $mDate)	
		->order(array('id_fk_region_stock_minerai', 'id_fk_type_stock_minerai'));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
	function findDistinctDate() {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('stock_minerai', 'distinct(date_stock_minerai) as date_stock_minerai')
		->order(array('date_stock_minerai DESC'));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}
