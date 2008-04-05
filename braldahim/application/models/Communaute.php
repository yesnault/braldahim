<?php

class Communaute extends Zend_Db_Table {
	protected $_name = 'communaute';
	protected $_primary = array('id_communaute');
	
	public function findById($id){
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('communaute', '*')
		->from('hobbit', '*')
		->where('id_fk_hobbit_createur_communaute = id_hobbit')
		->where('id_communaute = ?', intval($id));
		
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}
