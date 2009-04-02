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
class TypeEmplacement extends Zend_Db_Table {
	protected $_name = 'type_emplacement';
	protected $_primary = "id_type_emplacement";
	
	function findAllEquipable() {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('type_emplacement', '*')
		->where('est_equipable_type_emplacement = ?',"oui");
		
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}
}
