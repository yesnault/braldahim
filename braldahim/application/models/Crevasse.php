<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: $
 * $Author: $
 * $LastChangedDate: $
 * $LastChangedRevision: $
 * $LastChangedBy: $
 */
class Crevasse extends Zend_Db_Table {
	protected $_name = 'crevasse';
	protected $_primary = "id_crevasse";

	function countAll() {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('crevasse', 'count(id_crevasse) as nombre');
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		$nombre = $resultat[0]["nombre"];
		return $nombre;
	}

	function countVue($x_min, $y_min, $x_max, $y_max, $z) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('crevasse', 'count(id_crevasse) as nombre')
		->where('x_crevasse <= ?',$x_max)
		->where('x_crevasse >= ?',$x_min)
		->where('y_crevasse >= ?',$y_min)
		->where('y_crevasse <= ?',$y_max)
		->where('z_crevasse = ?',$z);
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);
		$nombre = $resultat[0]["nombre"];
		return $nombre;
	}

	function selectVue($x_min, $y_min, $x_max, $y_max, $z, $estDecouverte = null) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('crevasse', '*')
		->where('x_crevasse <= ?',$x_max)
		->where('x_crevasse >= ?',$x_min)
		->where('y_crevasse >= ?',$y_min)
		->where('y_crevasse <= ?',$y_max)
		->where('z_crevasse = ?',$z);
		if ($estDecouverte != null) {
			$select->where('est_decouverte_crevasse like ?', $estDecouverte);
		}
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findByCase($x, $y, $z) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('crevasse', '*')
		->where('x_crevasse = ?',$x)
		->where('y_crevasse = ?',$y)
		->where('z_crevasse = ?',$z);
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function countByCase($x, $y, $z) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('crevasse', 'count(id_crevasse) as nombre')
		->where('x_crevasse = ?',$x)
		->where('y_crevasse = ?',$y)
		->where('z_crevasse = ?',$z);
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);
		$nombre = $resultat[0]["nombre"];
		return $nombre;
	}
}
