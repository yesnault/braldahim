<?php

class Lieu extends Zend_Db_Table {
	protected $_name = 'lieu';
	protected $_primary = 'id_lieu';

	public function findByType($type){
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('lieu', '*')
		->from('type_lieu', '*')
		->from('ville', '*')
		->where('lieu.id_fk_type_lieu = ?',$type)
		->where('lieu.id_fk_type_lieu = type_lieu.id_type_lieu')
		->where('lieu.id_fk_ville_lieu = ville.id_ville');
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}

	function selectVue($x_min, $y_min, $x_max, $y_max) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('lieu', '*')
		->from('type_lieu', '*')
		->where('x_lieu <= ?',$x_max)
		->where('x_lieu >= ?',$x_min)
		->where('y_lieu >= ?',$y_min)
		->where('y_lieu <= ?',$y_max)
		->where('lieu.id_fk_type_lieu = type_lieu.id_type_lieu');
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}

	function findByCase($x, $y) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('lieu', '*')
		->from('type_lieu', '*')
		->from('ville', '*')
		->where('x_lieu = ?',$x)
		->where('y_lieu = ?',$y)
		->where('lieu.id_fk_type_lieu = type_lieu.id_type_lieu')
		->where('lieu.id_fk_ville_lieu = ville.id_ville');
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}
}