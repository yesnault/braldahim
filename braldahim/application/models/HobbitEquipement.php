<?php

class HobbitEquipement extends Zend_Db_Table {
	protected $_name = 'hobbits_equipement';
	protected $_primary = array('id_equipement_hequipement');

	function findByIdHobbit($id_hobbit) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('hobbits_equipement', '*')
		->from('recette_equipements')
		->from('type_equipement')
		->from('type_qualite')
		->from('type_emplacement')
		->where('id_fk_recette_hequipement = id_recette_equipement')
		->where('id_fk_type_recette_equipement = id_type_equipement')
		->where('id_fk_type_qualite_recette_equipement = id_type_qualite')
		->where('id_fk_type_emplacement_recette_equipement = id_type_emplacement')
		->where('id_fk_hobbit_hequipement = ?', intval($id_hobbit))
		->joinLeft('mot_runique','id_fk_mot_runique_hequipement = id_mot_runique');
		
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}
