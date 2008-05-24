<?php

class JosUsers extends Zend_Db_Table {
	protected $_name = 'jos_users';
	protected $_primary = 'id';
	
	protected function _setupDatabaseAdapter() {
		if (! $this->_db) {
			$this->_db = Zend_Registry::get('dbSiteAdapter');
			if (!$this->_db instanceof Zend_Db_Adapter_Abstract) {
				throw new Zend_Db_Table_Exception('Aucun adapter pour ' . get_class($this));
			}
		}
	}
	
	public function findByUsername($email) {
		$where = $this->getAdapter()->quoteInto('jos_users.username = ?',$email);
		return $this->fetchRow($where);
	}
}