<?php

class LabanPlante extends Zend_Db_Table {
	protected $_name = 'laban_plante';
	protected $_primary = 'id_laban_plante';
	
    function findByIdHobbit($id_hobbit) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('laban_plante', '*')
		->from('type_plante', '*')
		->where('id_hobbit_laban_plante = '.intval($id_hobbit))
		->where('laban_plante.id_fk_type_laban_plante = type_plante.id_type_plante');
		$sql = $select->__toString();

		return $db->fetchAll($sql);
    }
}
