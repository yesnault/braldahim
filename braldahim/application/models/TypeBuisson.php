<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id$
 * $Author$
 * $LastChangedDate$
 * $LastChangedRevision$
 * $LastChangedBy$
 */
class TypeBuisson extends Zend_Db_Table {
	protected $_name = 'type_buisson';
	protected $_primary = 'id_type_buisson';
	
	public function findById($id){
		$where = $this->getAdapter()->quoteInto('id_type_buisson = ?',(int)$id);
		return $this->fetchRow($where);
	}
}
