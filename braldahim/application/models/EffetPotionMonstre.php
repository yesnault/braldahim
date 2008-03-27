<?php

class EffetPotionMonstre extends Zend_Db_Table {
	protected $_name = 'effet_potion_monstre';
	protected $_primary = array('id_effet_potion_monstre');

	function findByIdMonstreCible($id_monstre) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('effet_potion_monstre', '*')
		->from('type_potion')
		->from('type_qualite')
		->where('id_fk_type_effet_potion_monstre = id_type_potion')
		->where('id_fk_type_qualite_effet_potion_monstre = id_type_qualite')
		->where('id_fk_monstre_cible_effet_potion_monstre = ?', intval($id_monstre));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}
