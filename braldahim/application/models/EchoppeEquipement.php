<?php

class EchoppeEquipement extends Zend_Db_Table {
	protected $_name = 'echoppe_equipement';
	protected $_primary = "id_echoppe_equipement";

	public function findByIdEchoppe($idEchoppe) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('echoppe_equipement', '*')
		->from('recette_equipements')
		->from('type_equipement')
		->from('type_qualite')
		->from('type_emplacement')
		->where('id_fk_recette_echoppe_equipement = id_recette_equipement')
		->where('id_fk_type_recette_equipement = id_type_equipement')
		->where('id_fk_type_qualite_recette_equipement = id_type_qualite')
		->where('id_fk_type_emplacement_recette_equipement = id_type_emplacement')
		->where('id_fk_echoppe_echoppe_equipement = ?', $idEchoppe);

		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}
}
