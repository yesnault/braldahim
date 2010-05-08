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
class BraldunsRoles extends Zend_Db_Table {
    protected $_name = 'bralduns_roles';
	
    function findByIdBraldun($idBraldun) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('bralduns_roles', '*')
		->from('role', '*')
		->where('bralduns_roles.id_fk_braldun_hroles = ?', intval($idBraldun))
		->where('bralduns_roles.id_fk_role_hroles = role.id_role');
		$sql = $select->__toString();
		return $db->fetchAll($sql);
    }
    
}