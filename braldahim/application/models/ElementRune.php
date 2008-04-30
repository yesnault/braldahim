<?php

class ElementRune extends Zend_Db_Table {
	protected $_name = 'element_rune';
	protected $_primary = 'id_element_rune';

	function selectVue($x_min, $y_min, $x_max, $y_max) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('element_rune', '*')
		->where('x_element_rune <= ?',$x_max)
		->where('x_element_rune >= ?',$x_min)
		->where('y_element_rune <= ?',$y_max)
		->where('y_element_rune >= ?',$y_min);
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}

	function findByCase($x, $y) {
		return $this->selectVue($x, $y, $x, $y);
	}
}