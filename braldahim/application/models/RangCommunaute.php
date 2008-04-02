<?php

class RangCommunaute extends Zend_Db_Table {
	protected $_name = 'rang_communaute';
	protected $_primary = array('id_fk_type_rang_communaute', 'id_fk_communaute_rang_communaute');
	
	function findByIdCommunaute($idCommunaute) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('rang_communaute')
		->where('id_fk_communaute_rang_communaute = ?', intval($idCommunaute))
		->order('id_fk_type_rang_communaute');
		
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}
