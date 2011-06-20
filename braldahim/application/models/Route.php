<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
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

	function countVue($x_min, $y_min, $x_max, $y_max, $z) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('route', 'count(id_route) as nombre')
		->where('x_route <= ?',$x_max)
		->where('x_route >= ?',$x_min)
		->where('y_route >= ?',$y_min)
		->where('y_route <= ?',$y_max)
		->where('z_route = ?',$z);
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);
		$nombre = $resultat[0]["nombre"];
		return $nombre;
	}

	function selectVue($x_min, $y_min, $x_max, $y_max, $z, $estVisible = 'oui') {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('route', '*')
		->where('x_route <= ?',$x_max)
		->where('x_route >= ?',$x_min)
		->where('y_route >= ?',$y_min)
		->where('y_route <= ?',$y_max)
		->where('z_route = ?',$z);
		if ($estVisible != "toutes") {
			$select->where('est_visible_route = ?', $estVisible);
		}
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findAllVisibleHorsBalise($limit = null, $offset = null) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('route', '*');
		$select->where('est_visible_route = ?', 'oui')
		->where('type_route not like ?', "balise");

		if ($limit != null && $offset != null) {
			$select->limit($limit, $offset);
		}

		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function countAllVisibleHorsBalise() {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('route', 'count(id_route) as nombre');
		$select->where('est_visible_route = ?', 'oui')
		->where('type_route not like ?', "balise");
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);
		$nombre = $resultat[0]["nombre"];
		return $nombre;
	}


	function findByCase($x, $y, $z, $estVisible = 'oui') {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('route', '*')
		->where('x_route = ?',$x)
		->where('y_route = ?',$y)
		->where('z_route = ?',$z);
		if ($estVisible != "toutes") {
			$select->where('est_visible_route = ?', $estVisible);
		}
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findByCaseHorsBalise($x, $y, $z) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('route', '*')
		->where('x_route = ?',$x)
		->where('y_route = ?',$y)
		->where('z_route = ?',$z)
		->where('type_route not like ?', "balise");
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
	function findByType($type) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('route', '*')
		->where('type_route not like ?', $type);
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
