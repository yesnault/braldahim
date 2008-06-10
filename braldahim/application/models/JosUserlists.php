<?php

class JosUserlists extends Zend_Db_Table {
	protected $_name = 'jos_uddeim_userlists';
	protected $_primary = 'id';
	
	protected function _setupDatabaseAdapter() {
		if (! $this->_db) {
			$this->_db = Zend_Registry::get('dbSiteAdapter');
			if (!$this->_db instanceof Zend_Db_Adapter_Abstract) {
				throw new Zend_Db_Table_Exception('Aucun adapter pour ' . get_class($this));
			}
		}
	}
	
	public function findByUserId($userId) {
		$db = $this->getAdapter();
		$select = $db->select();
		
		$select->from('jos_uddeim_userlists', '*')
		->where('jos_uddeim_userlists.userid = '.intval($userId));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
	public function findByIdList($idList, $userId) {
		$where = 'jos_uddeim_userlists.id = '.intval($idList);
		$where .= ' AND jos_uddeim_userlists.userid = '.intval($userId);
		return $this->fetchRow($where);
	}
}