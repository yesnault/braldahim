<?php

class EquipementRune extends Zend_Db_Table {
	protected $_name = 'equipement_rune';
	protected $_primary = array('id_equipement_rune', 'id_rune_equipement_rune');
	
    function findByIdEquipement($id_equipement) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('equipement_rune', '*')
		->from('type_rune', '*')
		->where('id_equipement_rune = ?', intval($id_equipement))
		->where('equipement_rune.id_fk_type_rune_equipement_rune = type_rune.id_type_rune');
		$sql = $select->__toString();

		return $db->fetchAll($sql);
    }
}
