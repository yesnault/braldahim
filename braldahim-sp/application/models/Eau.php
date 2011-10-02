<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: Eau.php 2806 2010-07-14 22:13:50Z yvonnickesnault $
 * $Author: yvonnickesnault $
 * $LastChangedDate: 2010-07-15 00:13:50 +0200 (jeu., 15 juil. 2010) $
 * $LastChangedRevision: 2806 $
 * $LastChangedBy: yvonnickesnault $
 */
class Eau extends Zend_Db_Table
{
	protected $_name = 'eau';
	protected $_primary = 'id_eau';

	function selectVue($x_min, $y_min, $x_max, $y_max, $z, $avecPeuProfonde = true)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('eau', '*')
			->where('x_eau <= ?', $x_max)
			->where('x_eau >= ?', $x_min)
			->where('y_eau >= ?', $y_min)
			->where('y_eau <= ?', $y_max)
			->where('z_eau = ?', $z);
		if ($avecPeuProfonde == false) {
			$select->where('type_eau not like ?', 'peuprofonde');
		}
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function countAll()
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('eau', 'count(*) as nombre');
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);
		$nombre = $resultat[0]["nombre"];
		return $nombre;
	}

	function countVue($x_min, $y_min, $x_max, $y_max, $z)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('eau', 'count(*) as nombre')
			->where('x_eau <= ?', $x_max)
			->where('x_eau >= ?', $x_min)
			->where('y_eau >= ?', $y_min)
			->where('y_eau <= ?', $y_max)
			->where('z_eau = ?', $z);

		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		$nombre = $resultat[0]["nombre"];
		return $nombre;
	}

	function countByCase($x, $y, $z)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('eau', 'count(*) as nombre')
			->where('x_eau = ?', $x)
			->where('y_eau = ?', $y)
			->where('z_eau = ?', $z);

		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		$nombre = $resultat[0]["nombre"];
		return $nombre;
	}

	function findByCase($x, $y, $z)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('eau', '*')
			->where('x_eau = ?', $x)
			->where('y_eau = ?', $y)
			->where('z_eau = ?', $z)
			->order('eau.id_eau');
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}
