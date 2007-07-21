<?php

class Message extends Zend_Db_Table {
	protected $_name = 'message';
	protected $_primary = 'id_message';

	public function findByIdHobbit($idHobbit, $idType, $page, $nbMax) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('message', '*')
		->where('message.id_fk_type_message = '.intval($idType))
		->where('message.id_fk_hobbit_message = '.intval($idHobbit))
		->order('date_envoi_message DESC')
		->limitPage($page, $nbMax);
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}