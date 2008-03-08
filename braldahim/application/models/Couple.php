<?php

class Couple extends Zend_Db_Table {
	protected $_name = 'couple';
	protected $_primary = array('id_fk_m_hobbit_couple', 'id_fk_f_hobbit_couple');
	
	function findAllEnfantPossible() {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('couple', '*')
		->where('nb_enfants_couple < ?', 5);
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}
