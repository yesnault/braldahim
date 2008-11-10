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
class TypePartieplante extends Zend_Db_Table {
	protected $_name = 'type_partieplante';
	protected $_primary = "id_type_partieplante";
	
	public function findById($id){
		$where = $this->getAdapter()->quoteInto('id_type_partieplante = ?',(int)$id);
		return $this->fetchRow($where);
	}
}
