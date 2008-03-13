<?php

class EquipementRune extends Zend_Db_Table {
	protected $_name = 'equipement_rune';
	protected $_primary = array('id_equipement_rune', 'id_rune_equipement_rune');
	
    function findByIdEquipement($id_equipement) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('equipement_rune', '*')
		->from('type_rune', '*')
		->where('id_equipement_rune = ?', (int)$id_equipement)
		->where('equipement_rune.id_fk_type_rune_equipement_rune = type_rune.id_type_rune');
		$sql = $select->__toString();

		return $db->fetchAll($sql);
    }
    
    function findByIdsEquipement($tabId) {
    	$where = "";
    	if ($tabId == null || count($tabId) == 0) {
    		return null;
    	}
    	
    	foreach($tabId as $id) {
			if ($where == "") {
				$or = "";
			} else {
				$or = " OR ";
			}
			$where .= " $or id_equipement_rune=".(int)$id;
    	}
    	
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('equipement_rune', '*')
		->from('type_rune', '*')
		->where($where)
		->where('equipement_rune.id_fk_type_rune_equipement_rune = type_rune.id_type_rune');
		$sql = $select->__toString();
		
		return $db->fetchAll($sql);
    }
}
