<?php

class Testeur extends Zend_Db_Table {
	protected $_name = 'testeur';
	protected $_primary = array('id_testeur');

	public function findByEmail($email){
		$where = $this->getAdapter()->quoteInto('lcase(email_testeur) = ?',(string)strtolower(trim($email)));
		return $this->fetchRow($where);
	}
}
