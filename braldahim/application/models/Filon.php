<?php

class Filon extends Zend_Db_Table {
	protected $_name = 'filon';
	protected $_primary = 'id_filon';

	function selectVue($x_min, $y_min, $x_max, $y_max) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('filon', '*')
		->from('type_filon', '*')
		->where('x_filon <= ?',$x_max)
		->where('x_filon >= ?',$x_min)
		->where('y_filon >= ?',$y_min)
		->where('y_filon <= ?',$y_max)
		->where('filon.id_fk_type_filon = type_filon.id_type_filon');
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
	function countVue($x_min, $y_min, $x_max, $y_max) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('filon', 'count(*) as nombre')
		->where('x_filon <= ?',$x_max)
		->where('x_filon >= ?',$x_min)
		->where('y_filon >= ?',$y_min)
		->where('y_filon <= ?',$y_max);
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		$nombre = $resultat[0]["nombre"];
		return $nombre;
	}
}
