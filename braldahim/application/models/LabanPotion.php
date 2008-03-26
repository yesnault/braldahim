<?php

class LabanPotion extends Zend_Db_Table {
	protected $_name = 'laban_potion';
	protected $_primary = array('id_laban_potion');

	function findByIdHobbit($id_hobbit) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('laban_potion', '*')
		->from('type_potion')
		->from('type_qualite')
		->where('id_fk_type_laban_potion = id_type_potion')
		->where('id_fk_type_qualite_laban_potion = id_type_qualite')
		->where('id_fk_hobbit_laban_potion = ?', intval($id_hobbit));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}
