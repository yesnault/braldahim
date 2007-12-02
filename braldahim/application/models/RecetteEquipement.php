<?php

class RecetteEquipement extends Zend_Db_Table {
	protected $_name = 'recette_equipements';
	protected $_primary = "id_recette_equipement";

	function findByIdTypeAndNiveauAndQualite($idType, $niveau, $qualite) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('recette_equipements', '*')
		->from('type_equipement')
		->from('type_qualite')
		->from('type_emplacement')
		->where('id_fk_type_recette_equipement = ?',$idType)
		->where('niveau_recette_equipement = ?',$niveau)
		->where('id_fk_type_qualite_recette_equipement = ?',$qualite)
		->where('id_fk_type_recette_equipement = id_type_equipement')
		->where('id_fk_type_qualite_recette_equipement = id_type_qualite')
		->where('id_fk_type_emplacement_recette_equipement = id_type_emplacement');

		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}
}
