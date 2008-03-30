<?php

class HobbitCommunaute extends Zend_Db_Table {
	protected $_name = 'hobbits_communaute';
	protected $_primary = array('id_fk_communaute_communaute', 'id_fk_hobbit_communaute');

	function findByIdHobbit($idHobbit) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('hobbits_communaute', '*')
		->from('communaute')
		->from('rang_communaute')
		->where('id_fk_communaute_communaute = id_communaute')
		->where('id_fk_rang_communaute_hobbit_communaute = id_fk_type_rang_communaute')
		->where('id_fk_hobbit_communaute = ?', intval($idHobbit));
		
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}
