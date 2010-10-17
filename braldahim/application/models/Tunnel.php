<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Tunel extends Zend_Db_Table {
	protected $_name = 'tunnel';
	protected $_primary = 'id_tunnel';

	function selectVue($x_min, $y_min, $x_max, $y_max, $z) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('tunnel', '*')
		->where('x_tunnel <= ?', $x_max)
		->where('x_tunnel >= ?', $x_min)
		->where('y_tunnel >= ?', $y_min)
		->where('y_tunnel <= ?', $y_max)
		->where('z_tunnel = ?', $z);

		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function countVue($x_min, $y_min, $x_max, $y_max, $z) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('tunnel', 'count(*) as nombre')
		->where('x_tunnel <= ?', $x_max)
		->where('x_tunnel >= ?', $x_min)
		->where('y_tunnel >= ?', $y_min)
		->where('y_tunnel <= ?', $y_max)
		->where('z_tunnel = ?', $z);

		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		$nombre = $resultat[0]["nombre"];
		return $nombre;
	}

	function findByCase($x, $y, $z) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('tunnel', '*')
		->where('x_tunnel = ?', $x)
		->where('y_tunnel = ?', $y)
		->where('z_tunnel = ?', $z)
		->order('tunnel.id_tunnel');
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}

}
