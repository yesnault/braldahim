<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Buisson extends Zend_Db_Table {
	protected $_name = 'buisson';
	protected $_primary = 'id_buisson';

	function selectVue($x_min, $y_min, $x_max, $y_max, $z) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('buisson', '*')
		->from('type_buisson', '*')
		->where('x_buisson <= ?',$x_max)
		->where('x_buisson >= ?',$x_min)
		->where('y_buisson >= ?',$y_min)
		->where('y_buisson <= ?',$y_max)
		->where('z_buisson = ?',$z)
		->where('buisson.id_fk_type_buisson_buisson = type_buisson.id_type_buisson');
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function countVue($x_min, $y_min, $x_max, $y_max, $z, $id_type = null) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('buisson', 'count(*) as nombre')
		->where('x_buisson <= ?',$x_max)
		->where('x_buisson >= ?',$x_min)
		->where('y_buisson >= ?',$y_min)
		->where('y_buisson <= ?',$y_max)
		->where('z_buisson = ?',$z);

		if ($id_type != null) {
			$select->where('id_fk_type_buisson_buisson = ?',$id_type);
		}

		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		$nombre = $resultat[0]["nombre"];
		return $nombre;
	}
	
	function countByCase($x, $y, $z) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('buisson', 'count(*) as nombre')
		->where('x_buisson = ?',$x)
		->where('y_buisson = ?',$y)
		->where('z_buisson = ?',$z);

		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		$nombre = $resultat[0]["nombre"];
		return $nombre;
	}

	function findByCase($x, $y, $z) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('buisson', '*')
		->from('type_buisson', '*')
		->where('x_buisson = ?',$x)
		->where('y_buisson = ?',$y)
		->where('z_buisson = ?',$z)
		->where('buisson.id_fk_type_buisson_buisson = type_buisson.id_type_buisson')
		->order('buisson.id_buisson');
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}

	function findLePlusProche($x, $y, $z, $rayon, $idTypeMinerai = null) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('buisson', 'id_buisson, x_buisson, y_buisson, id_fk_type_buisson_buisson, SQRT(((x_buisson - '.$x.') * (x_buisson - '.$x.')) + ((y_buisson - '.$y.') * ( y_buisson - '.$y.'))) as distance')
		->from('type_buisson', '*')
		->where('x_buisson >= ?', $x - $rayon)
		->where('x_buisson <= ?', $x + $rayon)
		->where('y_buisson >= ?', $y - $rayon)
		->where('y_buisson <= ?', $y + $rayon)
		->where('z_buisson = ?', $z)
		->where('buisson.id_fk_type_buisson_buisson = type_buisson.id_type_buisson')
		->order('distance ASC');

		if ($idTypeMinerai != null) {
			$select->where('id_fk_type_buisson_buisson = ?', $idTypeMinerai);	
		}

		$sql = $select->__toString();
		return $db->fetchRow($sql);
	}

	/**
	 * Supprime les buissons qui sont en ville.
	 */
	function deleteInVille() {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('ville', '*');

		$sql = $select->__toString();
		$villes = $db->fetchAll($sql);

		foreach($villes as $v) {
			$where = " x_buisson >= ". $v["x_min_ville"];
			$where .= " AND x_buisson <= ". $v["x_max_ville"];
			$where .= " AND y_buisson >= ". $v["y_min_ville"];
			$where .= " AND y_buisson <= ". $v["y_max_ville"];
			$this->delete($where);
		}
	}
}
