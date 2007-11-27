<?php

class RecetteCout extends Zend_Db_Table {
	protected $_name = 'recette_cout';
	protected $_primary = array('id_fk_type_equipement_recette_cout',
								'id_fk_type_recette_cout',
								'niveau_recette_cout'); 
	
	function findByIdTypeEquipement($idTypeEquipement) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('recette_cout', '*')
		->where('id_fk_type_equipement_recette_cout = ?',$idTypeEquipement);
		
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}
}
