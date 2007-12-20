<?php

class LabanEquipement extends Zend_Db_Table {
	protected $_name = 'laban_equipement';
	protected $_primary = array('id_laban_equipement');

	function findByIdHobbit($id_hobbit) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('laban', '*')
		->where('id_fk_hobbit_laban_equipement = '.intval($id_hobbit));
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}
}
