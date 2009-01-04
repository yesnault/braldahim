<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: Route.php 595 2008-11-09 11:21:27Z yvonnickesnault $
 * $Author: yvonnickesnault $
 * $LastChangedDate: 2008-11-09 12:21:27 +0100 (Sun, 09 Nov 2008) $
 * $LastChangedRevision: 595 $
 * $LastChangedBy: yvonnickesnault $
 */
class Route extends Zend_Db_Table {
	protected $_name = 'route';
	protected $_primary = "id_route";

	function countAll() {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('route', 'count(id_route) as nombre');
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		$nombre = $resultat[0]["nombre"];
		return $nombre;
	}

	function countVue($x_min, $y_min, $x_max, $y_max) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('route', 'count(id_route) as nombre')
		->where('x_route <= ?',$x_max)
		->where('x_route >= ?',$x_min)
		->where('y_route >= ?',$y_min)
		->where('y_route <= ?',$y_max);
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);
		$nombre = $resultat[0]["nombre"];
		return $nombre;
	}

	function selectVue($x_min, $y_min, $x_max, $y_max) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('route', '*')
		->where('x_route <= ?',$x_max)
		->where('x_route >= ?',$x_min)
		->where('y_route >= ?',$y_min)
		->where('y_route <= ?',$y_max);
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
	function findByCase($x, $y) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('route', '*')
		->where('x_route = ?',$x)
		->where('y_route = ?',$y);
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
	function findById($id) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('route', '*')
		->where('id_route = ?', $id);
		$sql = $select->__toString();
		return $db->fetchRow($sql);
	}
}
