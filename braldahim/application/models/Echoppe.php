<?php

class Echoppe extends Zend_Db_Table {
	protected $_name = 'echoppe';
	protected $_primary = "id_echoppe";

	function countAll() {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('echoppe', 'count(id_echoppe) as nombre');
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		$nombre = $resultat[0]["nombre"];
		return $nombre;
	}

	function countVue($x_min, $y_min, $x_max, $y_max) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('echoppe', 'count(id_echoppe) as nombre')
		->from('type_echoppe', '*')
		->from('metier', '*')
		->where('x_echoppe <= ?',$x_max)
		->where('x_echoppe >= ?',$x_min)
		->where('y_echoppe >= ?',$y_min)
		->where('y_echoppe <= ?',$y_max);
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);
		$nombre = $resultat[0]["nombre"];
		return $nombre;
	}

	function selectVue($x_min, $y_min, $x_max, $y_max) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('echoppe', '*')
		->from('type_echoppe', '*')
		->from('metier', '*')
		->where('x_echoppe <= ?',$x_max)
		->where('x_echoppe >= ?',$x_min)
		->where('y_echoppe >= ?',$y_min)
		->where('y_echoppe <= ?',$y_max)
		->where('echoppe.id_fk_type_echoppe = type_echoppe.id_type_echoppe')
		->where('type_echoppe.id_fk_metier_type_echoppe = metier.id_metier');
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
	function findByCase($x, $y) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('echoppe', '*')
		->from('type_echoppe', '*')
		->from('metier', '*')
		->where('x_echoppe = ?',$x)
		->where('y_echoppe = ?',$y)
		->where('echoppe.id_fk_type_echoppe = type_echoppe.id_type_echoppe')
		->where('type_echoppe.id_fk_metier_type_echoppe = metier.id_metier');
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findByIdHobbit($id_hobbit) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('echoppe', '*')
		->from('type_echoppe', '*')
		->from('metier', '*')
		->where('id_hobbit_echoppe = ?', $id_hobbit)
		->where('echoppe.id_fk_type_echoppe = type_echoppe.id_type_echoppe')
		->where('type_echoppe.id_fk_metier_type_echoppe = metier.id_metier');
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
	function findById($id) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('echopppe', '*')
		->from('type_echoppe', '*')
		->from('metier', '*')
		->where('id_echopppe = ?', $id)
		->where('echoppe.id_fk_type_echoppe = type_echoppe.id_type_echoppe')
		->where('type_echoppe.id_fk_metier_type_echoppe = metier.id_metier');
		$sql = $select->__toString();
		return $db->fetchRow($sql);
	}
}
