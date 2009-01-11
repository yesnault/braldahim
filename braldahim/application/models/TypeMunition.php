<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: TypeMunition.php 610 2008-11-10 15:18:35Z yvonnickesnault $
 * $Author: yvonnickesnault $
 * $LastChangedDate: 2008-11-10 16:18:35 +0100 (Mon, 10 Nov 2008) $
 * $LastChangedRevision: 610 $
 * $LastChangedBy: yvonnickesnault $
 */
class TypeMunition extends Zend_Db_Table {
	protected $_name = 'type_munition';
	protected $_primary = 'id_type_munition';
	
	public function findById($id){
		$where = $this->getAdapter()->quoteInto('id_type_munition = ?',(int)$id);
		return $this->fetchRow($where);
	}
}
