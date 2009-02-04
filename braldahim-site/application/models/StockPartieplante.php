<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: StockPartieplante.php 652 2008-11-28 17:01:17Z yvonnickesnault $
 * $Author: yvonnickesnault $
 * $LastChangedDate: 2008-11-28 18:01:17 +0100 (Fri, 28 Nov 2008) $
 * $LastChangedRevision: 652 $
 * $LastChangedBy: yvonnickesnault $
 */
class StockPartieplante extends Zend_Db_Table {
	protected $_name = 'stock_partieplante';
	protected $_primary = array('id_stock_partieplante');
	
	function findDernierStock() {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('stock_partieplante', array('max(date_stock_partieplante) as date_stock_partieplante'))
		->where('date_stock_partieplante <= ?', date("Y-m-d 23:59:59"));
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		$select = $db->select();
		$select->from('stock_partieplante', '*')
		->from('region')
		->from('type_partieplante')
		->from('type_plante')
		->where('id_fk_type_stock_partieplante = id_type_partieplante')
		->where('id_fk_type_plante_stock_partieplante = id_type_plante')
		->where('id_fk_region_stock_partieplante = id_region')
		->where('date_stock_partieplante = ?', $resultat[0]["date_stock_partieplante"])
		->order(array('nom_type_plante ASC', 'nom_type_partieplante'));
		
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
	function findByDate($mDate) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('stock_partieplante', '*')
		->from('type_partieplante', 'nom_type_partieplante')
		->from('type_plante', 'nom_type_plante')
		->from('region', 'nom_region')
		->where('stock_partieplante.id_fk_type_stock_partieplante = type_partieplante.id_type_partieplante')
		->where('stock_partieplante.id_fk_type_plante_stock_partieplante = type_plante.id_type_plante')
		->where('region.id_region = stock_partieplante.id_fk_region_stock_partieplante')
		->where('date_stock_partieplante  = ?', $mDate)	
		->order(array('id_fk_region_stock_partieplante', 'id_fk_type_plante_stock_partieplante', 'id_fk_type_stock_partieplante'));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
	function findDistinctDate() {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('stock_partieplante', 'distinct(date_stock_partieplante) as date_stock_partieplante')
		->order(array('date_stock_partieplante DESC'));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
}
