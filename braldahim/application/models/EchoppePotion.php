<?php

class EchoppePotion extends Zend_Db_Table {
	protected $_name = 'echoppe_potion';
	protected $_primary = "id_echoppe_potion";

	public function findByIdEchoppe($idEchoppe) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('echoppe_potion', '*')
		->from('type_potion')
		->from('type_qualite')
		->where('id_fk_type_potion = id_type_potion')
		->where('id_fk_type_qualite_recette_equipement = id_type_qualite')
		->where('id_fk_echoppe_echoppe_equipement = ?', $idEchoppe);

		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}
}
