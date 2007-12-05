<?php

class TypePlante extends Zend_Db_Table {
	protected $_name = 'type_plante';
	protected $_primary = 'id_type_plante';
	
	public function fetchAllAvecEnvironnement() {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('type_plante', '*')
		->from('environnement', '*')
		->where('type_plante.id_fk_environnement_type_plante = environnement.id_environnement');
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}
	
}