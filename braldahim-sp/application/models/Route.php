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
class Route extends Zend_Db_Table
{
	protected $_name = 'route';
	protected $_primary = "id_route";

	function countAll()
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('route', 'count(id_route) as nombre');
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		$nombre = $resultat[0]["nombre"];
		return $nombre;
	}

	function countVue($x_min, $y_min, $x_max, $y_max, $z)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('route', 'count(id_route) as nombre')
			->where('x_route <= ?', $x_max)
			->where('x_route >= ?', $x_min)
			->where('y_route >= ?', $y_min)
			->where('y_route <= ?', $y_max)
			->where('z_route = ?', $z)
			->where('est_visible_route like ?', 'oui');
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);
		$nombre = $resultat[0]["nombre"];
		return $nombre;
	}

	function selectVue($x_min, $y_min, $x_max, $y_max, $z)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('route', '*')
			->where('x_route <= ?', $x_max)
			->where('x_route >= ?', $x_min)
			->where('y_route >= ?', $y_min)
			->where('y_route <= ?', $y_max)
			->where('z_route = ?', $z)
			->where('est_visible_route like ?', 'oui');
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findByCase($x, $y, $z)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('route', '*')
			->where('x_route = ?', $x)
			->where('y_route = ?', $y)
			->where('z_route = ?', $z)
			->where('est_visible_route like ?', 'oui');
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findById($id)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('route', '*')
			->where('id_route = ?', $id);
		$sql = $select->__toString();
		return $db->fetchRow($sql);
	}
}
