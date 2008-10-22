<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id:$
 * $Author:$
 * $LastChangedDate:$
 * $LastChangedRevision:$
 * $LastChangedBy:$
 */
class Palissade extends Zend_Db_Table {
	protected $_name = 'palissade';
	protected $_primary = "id_palissade";

	function countAll() {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('palissade', 'count(id_palissade) as nombre');
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		$nombre = $resultat[0]["nombre"];
		return $nombre;
	}

	function countVue($x_min, $y_min, $x_max, $y_max) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('palissade', 'count(id_palissade) as nombre')
		->where('x_palissade <= ?',$x_max)
		->where('x_palissade >= ?',$x_min)
		->where('y_palissade >= ?',$y_min)
		->where('y_palissade <= ?',$y_max);
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);
		$nombre = $resultat[0]["nombre"];
		return $nombre;
	}

	function selectVue($x_min, $y_min, $x_max, $y_max) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('palissade', '*')
		->where('x_palissade <= ?',$x_max)
		->where('x_palissade >= ?',$x_min)
		->where('y_palissade >= ?',$y_min)
		->where('y_palissade <= ?',$y_max);
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
	function findByCase($x, $y) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('palissade', '*')
		->where('x_palissade = ?',$x)
		->where('y_palissade = ?',$y);
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
	function findById($id) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('palissade', '*')
		->where('id_palissade = ?', $id);
		$sql = $select->__toString();
		return $db->fetchRow($sql);
	}
}
