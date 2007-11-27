<?php

class TypeEquipement extends Zend_Db_Table {
	protected $_name = 'type_equipement';
	protected $_primary = "id_type_equipement";
	
	function findByIdMetier($idMetier) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('type_equipement', '*')
		->where('id_fk_metier_type_equipement = ?',$idMetier)
		->order('nom_type_equipement');
		
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}
}
