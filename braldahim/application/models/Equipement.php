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
class Equipement extends Zend_Db_Table {
	protected $_name = 'equipement';
	protected $_primary = array('id_equipement');
	
    function findByIdEquipement($id_equipement) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('equipement', '*')
		->where('id_equipement = ?', (int)$id_equipement);
		$sql = $select->__toString();

		return $db->fetchAll($sql);
    }
    
    function findByIdsEquipement($tabId) {
    	$where = "";
    	if ($tabId == null || count($tabId) == 0) {
    		return null;
    	}
    	
    	foreach($tabId as $id) {
			if ($where == "") {
				$or = "";
			} else {
				$or = " OR ";
			}
			$where .= " $or id_equipement=".(int)$id;
    	}
    	
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('equipement', '*')
		->where($where);
		$sql = $select->__toString();
		
		return $db->fetchAll($sql);
    }
}
