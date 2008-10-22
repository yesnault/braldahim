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