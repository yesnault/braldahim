<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id:$
 * $Author:$
 * $LastChangedDate:$
 * $LastChangedRevision:$
 * $LastChangedBy:$
 */
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
		->where('jos_uddeim_userlists.userid = '.intval($userId))
		->order('jos_uddeim_userlists.name');
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
	public function findByIdList($idList, $userId) {
		$where = 'jos_uddeim_userlists.id = '.intval($idList);
		$where .= ' AND jos_uddeim_userlists.userid = '.intval($userId);
		return $this->fetchRow($where);
	}
	
	public function findByIdsList($listIds, $userId) {
		return $this->findByList("jos_uddeim_userlists.id", $listIds, $userId);
	}
	
	private function findByList($nomChamp, $listIds, $userId) {
		$liste = "";
		if (count($listIds) < 1) {
			$liste = "";
		} else {
			foreach($listIds as $id) {
				if ((int) $id."" == $id."") {
					if ($liste == "") {
						$liste = $id;
					} else {
						$liste = $liste." OR ".$nomChamp."=".$id;
					}
				}
			}
		}
		
		if ($liste != "") {
			$liste = $liste . ' AND jos_uddeim_userlists.userid = '.intval($userId);
		} else {
			$liste = 'jos_uddeim_userlists.userid = '.intval($userId);
		}
		
		if ($liste != "") {
			$db = $this->getAdapter();
			$select = $db->select();
			$select->from('jos_uddeim_userlists', '*')
			->where($nomChamp .'='. $liste);
			$sql = $select->__toString();
			return $db->fetchAll($sql);
		} else {
			return null;
		}
	}
}