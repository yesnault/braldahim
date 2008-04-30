<?php

class ElementPotion extends Zend_Db_Table {
	protected $_name = 'element_potion';
	protected $_primary = array('id_element_potion');

	function selectVue($x_min, $y_min, $x_max, $y_max) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('element_potion', '*')
		->from('type_potion')
		->from('type_qualite')
		->where('id_fk_type_element_potion = id_type_potion')
		->where('id_fk_type_qualite_element_potion = id_type_qualite')
		->where('x_element_potion <= ?', $x_max)
		->where('x_element_potion >= ?', $x_min)
		->where('y_element_potion <= ?', $y_max)
		->where('y_element_potion >= ?', $y_min);
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findByCase($x, $y) {
		return $this->selectVue($x, $y, $x, $y);
	}
}
