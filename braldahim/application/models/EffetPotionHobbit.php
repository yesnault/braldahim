<?php

class EffetPotionHobbit extends Zend_Db_Table {
	protected $_name = 'effet_potion_hobbit';
	protected $_primary = array('id_effet_potion_hobbit');

	function findByIdHobbitCible($id_hobbit) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('effet_potion_hobbit', '*')
		->from('type_potion')
		->from('type_qualite')
		->where('id_fk_type_effet_potion_hobbit = id_type_potion')
		->where('id_fk_type_qualite_effet_potion_hobbit = id_type_qualite')
		->where('id_fk_hobbit_cible_effet_potion_hobbit = ?', intval($id_hobbit));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}
