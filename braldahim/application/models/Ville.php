<?php

class Ville extends Zend_Db_Table {
	protected $_name = 'ville';
	protected $_primary = 'id_ville';

	function selectVue($x_min, $y_min, $x_max, $y_max) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('ville', '*')
		->from('region', '*')
		->where('x_min_ville <= ?',$x_max)
		->where('x_max_ville >= ?',$x_min)
		->where('y_min_ville <= ?',$y_max)
		->where('y_max_ville >= ?',$y_min)
		->where('ville.id_fk_region_ville = region.id_region');
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}

	function findByCase($x, $y) {
		return $this->selectVue($x, $y, $x, $y);
	}
}