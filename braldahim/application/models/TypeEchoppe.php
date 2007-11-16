<?php

class TypeEchoppe extends Zend_Db_Table {
	protected $_name = 'type_echoppe';
	protected $_primary = 'id_type_echoppe';

	public function peutPossederEchoppeIdHobbit($idHobbit) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('hobbits_metiers', 'count(id_metier_hmetier) as nombre')
		->from('type_echoppe', 'id_type_echoppe')
		->where('type_echoppe.id_fk_metier_type_echoppe = hobbits_metiers.id_metier_hmetier')
		->where('hobbits_metiers.id_hobbit_hmetier = '.intval($idHobbit))
		->group('id_type_echoppe');
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);
		
		if (!isset($resultat[0]) || $resultat[0]["nombre"] <1) {
			return false;
		} else {
			return true;
		}
	}
}