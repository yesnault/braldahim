<?php

class Rune extends Zend_Db_Table {
	protected $_name = 'rune';
	protected $_primary = 'id_rune';

	function selectVue($x_min, $y_min, $x_max, $y_max) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('rune', '*')
		->where('x_rune <= ?',$x_max)
		->where('x_rune >= ?',$x_min)
		->where('y_rune <= ?',$y_max)
		->where('y_rune >= ?',$y_min);
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}

	function findByCase($x, $y) {
		return $this->selectVue($x, $y, $x, $y);
	}
}