<?php

class LabanEquipement extends Zend_Db_Table {
	protected $_name = 'laban_equipement';
	protected $_primary = array('id_laban_equipement');

	function findByIdHobbit($id_hobbit) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('laban_equipement', '*')
		->from('recette_equipements')
		->from('type_equipement')
		->from('type_qualite')
		->from('type_emplacement')
		->where('id_fk_recette_laban_equipement = id_recette_equipement')
		->where('id_fk_type_recette_equipement = id_type_equipement')
		->where('id_fk_type_qualite_recette_equipement = id_type_qualite')
		->where('id_fk_type_emplacement_recette_equipement = id_type_emplacement')
		->where('id_fk_hobbit_laban_equipement = ?', intval($id_hobbit));
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}
}
