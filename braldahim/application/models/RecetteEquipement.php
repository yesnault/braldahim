<?php

class RecetteEquipement extends Zend_Db_Table {
	protected $_name = 'recette_equipements';
	protected $_primary = "id_recette_equipement";
	
	function findByIdType($idType) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('recette_equipements', '*')
		->from('type_equipement')
		->where('id_fk_type_recette_equipement = ?',$idType)
		->where('id_fk_type_recette_equipement = id_type_equipement');
		
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}
}
