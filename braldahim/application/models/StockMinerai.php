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

	function findDernierStockByIdRegion($idRegion, $idTypeMinerai = null) {
		
		$where = "";
		if ($idTypeMinerai != null) {
			$where = " id_fk_type_stock_minerai=".$idTypeMinerai. ' AND ';
		}
		
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('stock_minerai', array('max(date_stock_minerai) as date_stock_minerai'))
		->where($where.'id_fk_region_stock_minerai  = ?', $idRegion)
		->where('date_stock_minerai < ?', date("Y-m-d 23:59:59"));
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		if (count($resultat) != 1) {
			throw new Zend_Exception("StockMinerai::findDernierStockByIdRegion count invalide:".count($resultat). " idregion:".$idRegion);
		}
		
		$select = $db->select();
		$select->from('stock_minerai', '*')
		->where('id_fk_region_stock_minerai  = ?', $idRegion)
		->where($where.'date_stock_minerai = ?', $resultat[0]["date_stock_minerai"]);
		
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
	
	public function updateStock($data) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('stock_minerai', 'nb_brut_restant_stock_minerai as quantiteBrutRestant')
		->where('id_stock_minerai = ?',$data["id_stock_minerai"]);
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		if (count($resultat) != 1) {
			throw new Zend_Exception("StockMinerai::updateStock count invalide:".count($resultat). " id_stock_minerai:".$data["id_stock_minerai"]);
		}
		
		$quantiteBrutRestant = $resultat[0]["quantiteBrutRestant"];
		$dataUpdate['nb_brut_restant_stock_minerai'] = $quantiteBrutRestant + $data["nb_brut_restant_stock_minerai"];
		
		if (isset($dataUpdate)) {
			$where = 'id_stock_minerai = '.$data["id_stock_minerai"];
			$this->update($dataUpdate, $where);
		}
	}
}
