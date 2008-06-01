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

	public function findById($idUser, $id) {
		$db = $this->getAdapter();
		$select = $db->select();
		
		$select->from('jos_uddeim', '*')
		->where('jos_uddeim.id = '.intval($id))
		->where('jos_uddeim.toid = '.intval($idUser). ' OR jos_uddeim.fromid = '.intval($idUser));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
	public function findByToId($toId, $page, $nbMax) {
		$db = $this->getAdapter();
		$select = $db->select();
		
		$select->from('jos_uddeim', '*')
		->where('jos_uddeim.toid = '.intval($toId))
		->where('jos_uddeim.totrash = 0')
		->order('datum DESC')
		->limitPage($page, $nbMax);
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	
	public function findByFromId($toId, $page, $nbMax) {
		$db = $this->getAdapter();
		$select = $db->select();
		
		$select->from('jos_uddeim', '*')
		->where('jos_uddeim.fromid = '.intval($toId))
		->where('jos_uddeim.totrash = 0')
		->order('datum DESC')
		->limitPage($page, $nbMax);
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
	public function findByToOrFromIdSupprime($toOrFromId, $page, $nbMax) {
		$db = $this->getAdapter();
		$select = $db->select();
		
		$select->from('jos_uddeim', '*')
		->where('jos_uddeim.toid = '.intval($toOrFromId). ' OR jos_uddeim.fromid = '.intval($toOrFromId))
		->where('jos_uddeim.totrash = 1')
		->order('datum DESC')
		->limitPage($page, $nbMax);
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}