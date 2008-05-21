<?php

class JosUddeim extends Zend_Db_Table {
	protected $_name = 'jos_uddeim';
	protected $_primary = 'id';
	
	protected function _setupDatabaseAdapter() {
		if (! $this->_db) {
			$this->_db = Zend_Registry::get('dbSiteAdapter');
			if (!$this->_db instanceof Zend_Db_Adapter_Abstract) {
				throw new Zend_Db_Table_Exception('Aucun adapter pour ' . get_class($this));
			}
		}
	}
	
	public function findByToId($toId, $page, $nbMax) {
		$db = $this->getAdapter();
		$select = $db->select();
		
		$select->from('jos_uddeim', '*')
		->where('jos_uddeim.toid = '.intval($toId))
		->order('datum DESC')
		->limitPage($page, $nbMax);
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
}