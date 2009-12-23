<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: Bosquet.php 2031 2009-09-25 06:25:32Z yvonnickesnault $
 * $Author: yvonnickesnault $
 * $LastChangedDate: 2009-09-25 08:25:32 +0200 (ven., 25 sept. 2009) $
 * $LastChangedRevision: 2031 $
 * $LastChangedBy: yvonnickesnault $
 */
class Bosquet extends Zend_Db_Table {
	protected $_name = 'bosquet';
	protected $_primary = 'id_bosquet';

	function selectVue($x_min, $y_min, $x_max, $y_max, $z) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('bosquet', '*')
		->from('type_bosquet', '*')
		->where('x_bosquet <= ?',$x_max)
		->where('x_bosquet >= ?',$x_min)
		->where('y_bosquet >= ?',$y_min)
		->where('y_bosquet <= ?',$y_max)
		->where('z_bosquet = ?',$z)
		->where('bosquet.id_fk_type_bosquet_bosquet = type_bosquet.id_type_bosquet');
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function countVue($x_min, $y_min, $x_max, $y_max, $z, $id_type = null) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('bosquet', 'count(*) as nombre')
		->where('x_bosquet <= ?',$x_max)
		->where('x_bosquet >= ?',$x_min)
		->where('y_bosquet >= ?',$y_min)
		->where('y_bosquet <= ?',$y_max)
		->where('z_bosquet = ?',$z);

		if ($id_type != null) {
			$select->where('id_fk_type_bosquet_bosquet = ?',$id_type);
		}

		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		$nombre = $resultat[0]["nombre"];
		return $nombre;
	}
	
	function countByCase($x, $y, $z) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('bosquet', 'count(*) as nombre')
		->where('x_bosquet = ?',$x)
		->where('y_bosquet = ?',$y)
		->where('z_bosquet = ?',$z);

		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		$nombre = $resultat[0]["nombre"];
		return $nombre;
	}

	function findByCase($x, $y, $z) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('bosquet', '*')
		->from('type_bosquet', '*')
		->where('x_bosquet = ?',$x)
		->where('y_bosquet = ?',$y)
		->where('z_bosquet = ?',$z)
		->where('bosquet.id_fk_type_bosquet_bosquet = type_bosquet.id_type_bosquet')
		->order('bosquet.id_bosquet');
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}

	function findLePlusProche($x, $y, $z, $rayon, $idTypeMinerai = null) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('bosquet', 'id_bosquet, x_bosquet, y_bosquet, id_fk_type_bosquet_bosquet, SQRT(((x_bosquet - '.$x.') * (x_bosquet - '.$x.')) + ((y_bosquet - '.$y.') * ( y_bosquet - '.$y.'))) as distance')
		->from('type_bosquet', '*')
		->where('x_bosquet >= ?', $x - $rayon)
		->where('x_bosquet <= ?', $x + $rayon)
		->where('y_bosquet >= ?', $y - $rayon)
		->where('y_bosquet <= ?', $y + $rayon)
		->where('z_bosquet = ?', $z)
		->where('bosquet.id_fk_type_bosquet_bosquet = type_bosquet.id_type_bosquet')
		->order('distance ASC');

		if ($idTypeMinerai != null) {
			$select->where('id_fk_type_bosquet_bosquet = ?', $idTypeMinerai);	
		}

		$sql = $select->__toString();
		return $db->fetchRow($sql);
	}

	/**
	 * Supprime les bosquets qui sont en ville.
	 */
	function deleteInVille() {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('ville', '*');

		$sql = $select->__toString();
		$villes = $db->fetchAll($sql);

		foreach($villes as $v) {
			$where = " x_bosquet >= ". $v["x_min_ville"];
			$where .= " AND x_bosquet <= ". $v["x_max_ville"];
			$where .= " AND y_bosquet >= ". $v["y_min_ville"];
			$where .= " AND y_bosquet <= ". $v["y_max_ville"];
			$this->delete($where);
		}
	}
}
