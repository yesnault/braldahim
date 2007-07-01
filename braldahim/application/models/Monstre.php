<?php

class Monstre extends Zend_Db_Table {
	protected $_name = 'monstre';
	protected $_primary = "id_monstre";

	function countAll() {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('monstre', 'count(id_monstre) as nombre')
		->where('est_mort_monstre = ?', 'non');
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		$nombre = $resultat[0]["nombre"];
		return $nombre;
	}

	function countAllByType($id_type) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('monstre', 'count(id_monstre) as nombre')
		->where('id_fk_type_monstre = ?', intval($id_type));
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		$nombre = $resultat[0]["nombre"];
		return $nombre;
	}

	function countAllByTaille($id_taille) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('monstre', 'count(id_monstre) as nombre')
		->where('id_fk_taille_monstre = ?', intval($id_taille));
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		$nombre = $resultat[0]["nombre"];
		return $nombre;
	}

	function countVue($x_min, $y_min, $x_max, $y_max) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('monstre', 'count(id_monstre) as nombre')
		->where('x_monstre <= ?',$x_max)
		->where('x_monstre >= ?',$x_min)
		->where('y_monstre >= ?',$y_min)
		->where('y_monstre <= ?',$y_max)
		->where('est_mort_monstre = ?', 'non');
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		$nombre = $resultat[0]["nombre"];
		return $nombre;
	}
}
