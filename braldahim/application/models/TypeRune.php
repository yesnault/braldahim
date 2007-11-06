<?php

class TypeRune extends Zend_Db_Table {
	protected $_name = 'type_rune';
	protected $_primary = 'id_type_rune';
	
	function findByTirage($tirage) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('type_rune', '*')
		->where('tirage_type_rune <= ?',$tirage);
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}
}