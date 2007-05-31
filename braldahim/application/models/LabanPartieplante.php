<?php

class LabanPartieplante extends Zend_Db_Table {
	protected $_name = 'laban_partieplante';
	protected $_primary = array('id_fk_type_laban_partieplante', 'id_hobbit_laban_partieplante');
	
    function findByIdHobbit($id_hobbit) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('laban_partieplante', '*')
		->from('type_partieplante', '*')
		->where('id_hobbit_laban_partieplante = '.intval($id_hobbit))
		->where('laban_partieplante.id_fk_type_laban_partieplante = type_partieplante.id_type_partieplante');
		$sql = $select->__toString();

		return $db->fetchAll($sql);
    }
}
