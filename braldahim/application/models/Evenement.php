<?php

class Evenement extends Zend_Db_Table {
	protected $_name = 'evenement';
	protected $_primary = 'id_evenement';

	public function findByIdHobbit($idHobbit){
		$where = $this->getAdapter()->quoteInto("id_hobbit_evenement = ?", $idHobbit);
		return $this->fetchAll($where);
	}
}