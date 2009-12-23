<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: Palissade.php 2030 2009-09-24 11:43:22Z yvonnickesnault $
 * $Author: yvonnickesnault $
 * $LastChangedDate: 2009-09-24 13:43:22 +0200 (jeu., 24 sept. 2009) $
 * $LastChangedRevision: 2030 $
 * $LastChangedBy: yvonnickesnault $
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

	function countVue($x_min, $y_min, $x_max, $y_max, $z) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('palissade', 'count(id_palissade) as nombre')
		->where('x_palissade <= ?',$x_max)
		->where('x_palissade >= ?',$x_min)
		->where('y_palissade >= ?',$y_min)
		->where('y_palissade <= ?',$y_max)
		->where('z_palissade = ?',$z);
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);
		$nombre = $resultat[0]["nombre"];
		return $nombre;
	}
	
	function countCase($x, $y) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('palissade', 'count(id_palissade) as nombre')
		->where('x_palissade = ?',$x)
		->where('y_palissade = ?',$y);
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);
		$nombre = $resultat[0]["nombre"];
		return $nombre;
	}

	function selectVue($x_min, $y_min, $x_max, $y_max, $z) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('palissade', '*')
		->where('x_palissade <= ?',$x_max)
		->where('x_palissade >= ?',$x_min)
		->where('y_palissade >= ?',$y_min)
		->where('y_palissade <= ?',$y_max)
		->where('z_palissade = ?',$z);
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
	function findByCase($x, $y, $z) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('palissade', '*')
		->where('x_palissade = ?',$x)
		->where('y_palissade = ?',$y)
		->where('z_palissade = ?',$z);
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
