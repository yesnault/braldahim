<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class TypeBosquet extends Zend_Db_Table {
	protected $_name = 'type_bosquet';
	protected $_primary = 'id_type_bosquet';
	
	public function findById($id){
		$where = $this->getAdapter()->quoteInto('id_type_bosquet = ?',(int)$id);
		return $this->fetchRow($where);
	}
}
