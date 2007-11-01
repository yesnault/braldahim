<?php

class GroupeMonstre extends Zend_Db_Table {
	protected $_name = 'groupe_monstre';
	protected $_primary = "id_groupe_monstre";

	function findGroupesAJouer($nombreMax, $idTypeGroupe) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('groupe_monstre', '*')
		->from('type_groupe_monstre', '*')
		->where('groupe_monstre.id_fk_type_groupe_monstre = type_groupe_monstre.id_type_groupe_monstre')
		->where('groupe_monstre.id_fk_type_groupe_monstre = '.$idTypeGroupe)
		->order('date_fin_tour_groupe_monstre ASC')
		->limitPage(0, $nombreMax);
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
	function countAll() {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('groupe_monstre', 'count(id_groupe_monstre) as nombre');
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		$nombre = $resultat[0]["nombre"];
		return $nombre;
	}
}
