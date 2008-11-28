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
	
	function findDernierStockByIdRegion($idRegion, $idTypePartiePlante = null, $idTypePlante = null) {
		
		$where = "";
		if ($idTypePartiePlante != null && $idTypePlante != null) {
			$where = " id_fk_type_stock_partieplante=".$idTypePartiePlante. ' AND ';
			$where .= " id_fk_type_plante_stock_partieplante=".$idTypePlante. ' AND ';
		}
		
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('stock_partieplante', array('max(date_stock_partieplante) as date_stock_partieplante'))
		->where($where.'id_fk_region_stock_partieplante  = ?', $idRegion)
		->where('date_stock_partieplante < ?', date("Y-m-d 23:59:59"));
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		if (count($resultat) != 1) {
			throw new Zend_Exception("StockMinerai::findDernierStockByIdRegion count invalide:".count($resultat). " idregion:".$idRegion);
		}
		
		$select = $db->select();
		$select->from('stock_partieplante', '*')
		->where('id_fk_region_stock_partieplante  = ?', $idRegion)
		->where($where.'date_stock_partieplante = ?', $resultat[0]["date_stock_partieplante"]);
		
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
	
	public function updateStock($data) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('stock_partieplante', 'nb_brut_restant_stock_partieplante as quantiteBrutRestant')
		->where('id_stock_partieplante = ?',$data["id_stock_partieplante"]);
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		if (count($resultat) != 1) {
			throw new Zend_Exception("StockMinerai::updateStock count invalide:".count($resultat). " id_stock_partieplante:".$data["id_stock_partieplante"]);
		}
		
		$quantiteBrutRestant = $resultat[0]["quantiteBrutRestant"];
		$dataUpdate['nb_brut_restant_stock_partieplante'] = $quantiteBrutRestant + $data["nb_brut_restant_stock_partieplante"];
		
		if (isset($dataUpdate)) {
			$where = 'id_stock_partieplante = '.$data["id_stock_partieplante"];
			$this->update($dataUpdate, $where);
		}
	}
}
