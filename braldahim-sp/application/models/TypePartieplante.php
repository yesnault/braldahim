<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: TypePartieplante.php 612 2008-11-10 22:16:47Z yvonnickesnault $
 * $Author: yvonnickesnault $
 * $LastChangedDate: 2008-11-10 23:16:47 +0100 (lun., 10 nov. 2008) $
 * $LastChangedRevision: 612 $
 * $LastChangedBy: yvonnickesnault $
 */
class TypePartieplante extends Zend_Db_Table {
	protected $_name = 'type_partieplante';
	protected $_primary = "id_type_partieplante";
	
	public function findById($id){
		$where = $this->getAdapter()->quoteInto('id_type_partieplante = ?',(int)$id);
		return $this->fetchRow($where);
	}
}
