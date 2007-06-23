<?php

class Evenement extends Zend_Db_Table {
	protected $_name = 'evenement';
	protected $_primary = 'id_evenement';

	public function findByIdHobbit($idHobbit, $pageMin, $pageMax){
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('evenement', '*')
		->from('type_evenement', '*')
		->where('evenement.id_fk_type_evenement = type_evenement.id_type_evenement')
		->where('evenement.id_hobbit_evenement = '.intval($idHobbit))
		->order('date_evenement')
		->limitPage($pageMin, $pageMax);
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}