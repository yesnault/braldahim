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
	
	public function findDernierStockByIdRegion($idRegion) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('stock_bois', array('max(date_stock_bois) as date_stock_bois'))
		->where('id_fk_region_stock_bois  = ?', $idRegion)
		->where('date_stock_bois <= ?', date("Y-m-d 23:59:59"));
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		if (count($resultat) != 1) {
			throw new Zend_Exception("StockBois::findDernierStockByIdRegion count invalide:".count($resultat). " idregion:".$idRegion);
		}
		
		$select = $db->select();
		$select->from('stock_bois', '*')
		->where('id_fk_region_stock_bois  = ?', $idRegion)
		->where('date_stock_bois = ?', $resultat[0]["date_stock_bois"]);
		
		$sql = $select->__toString();
		
		return $db->fetchAll($sql);
	}
	
	public function findByDate($mDate) {
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
	
	public function findDistinctDate() {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('stock_bois', 'distinct(date_stock_bois) as date_stock_bois')
		->order(array('date_stock_bois DESC'));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
	public function updateStock($data) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('stock_bois', 'nb_rondin_restant_stock_bois as quantiteRondinRestant')
		->where('id_stock_bois = ?',$data["id_stock_bois"]);
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		if (count($resultat) != 1) {
			throw new Zend_Exception("StockBois::updateStock count invalide:".count($resultat). " id_stock_bois:".$data["id_stock_bois"]);
		}
		
		$quantiteRondinRestant = $resultat[0]["quantiteRondinRestant"];
		$dataUpdate['nb_rondin_restant_stock_bois'] = $quantiteRondinRestant + $data["nb_rondin_restant_stock_bois"];
		
		if (isset($dataUpdate)) {
			$where = 'id_stock_bois = '.$data["id_stock_bois"];
			$this->update($dataUpdate, $where);
		}
	}
}
