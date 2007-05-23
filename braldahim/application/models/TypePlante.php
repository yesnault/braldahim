<?php

class TypePlante extends Zend_Db_Table {
	protected $_name = 'type_plante';

	public function fetchAllAvecEnvironnement() {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('type_plante', '*')
		->from('environnement', '*')
		->where('type_plante.id_fk_environnement_type_plante = environnement.id');
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}
}