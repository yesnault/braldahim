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
class HobbitsRoles extends Zend_Db_Table {
    protected $_name = 'hobbits_roles';
	
    function findByIdHobbit($idHobbit) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('hobbits_roles', '*')
		->from('role', '*')
		->where('hobbits_roles.id_fk_hobbit_hroles = ?', intval($idHobbit))
		->where('hobbits_roles.id_fk_role_hroles = role.id_role');
		$sql = $select->__toString();
		return $db->fetchAll($sql);
    }
    
}