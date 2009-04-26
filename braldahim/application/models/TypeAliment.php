<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: $
 * $Author: $
 * $LastChangedDate: $
 * $LastChangedRevision: $
 * $LastChangedBy: $
 */
class TypeAliment extends Zend_Db_Table {
	protected $_name = 'type_aliment';
	protected $_primary = "id_type_aliment";
	
	const ID_TYPE_RAGOUT = 1;
	
	public function findById($id){
		$where = $this->getAdapter()->quoteInto('id_type_aliment = ?',(int)$id);
		return $this->fetchRow($where);
	}
}
