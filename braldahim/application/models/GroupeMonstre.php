<?php

class GroupeMonstre extends Zend_Db_Table {
	protected $_name = 'groupe_monstre';
	protected $_primary = "id_groupe_monstre";

	function findGroupesAJouer($nombreMax) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('groupe_monstre', '*')
		->from('type_groupe_monstre', '*')
		->where('groupe_monstre.id_fk_type_groupe_monstre = type_groupe_monstre.id_type_groupe_monstre')
		->order('date_fin_tour_groupe_monstre ASC')
		->limitPage(0, $nombreMax);
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}
