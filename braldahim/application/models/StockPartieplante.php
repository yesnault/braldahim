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
class StockPartieplante extends Zend_Db_Table {
	protected $_name = 'stock_partieplante';
	protected $_primary = array('id_stock_partieplante');
	
	function findDernierStockByIdRegion($idRegion) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('stock_partieplante', array('max(date_stock_partieplante) as date_stock_partieplante', 'id_fk_type_stock_partieplante', 'id_fk_type_plante_stock_partieplante', 'nb_brut_initial_stock_partieplante', 'nb_brut_restant_stock_partieplante', 'prix_unitaire_vente_stock_partieplante', 'prix_unitaire_reprise_stock_partieplante'))
		->where('id_fk_region_stock_partieplante  = ?', $idRegion)
		->group(array('id_fk_type_stock_partieplante', 'id_fk_type_plante_stock_partieplante', 'nb_brut_initial_stock_partieplante', 'nb_brut_restant_stock_partieplante', 'prix_unitaire_vente_stock_partieplante', 'prix_unitaire_reprise_stock_partieplante'));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
	
	function findByDate($mDate) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('stock_partieplante', '*')
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
