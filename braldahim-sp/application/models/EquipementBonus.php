<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: EquipementBonus.php 1408 2009-03-29 16:26:37Z yvonnickesnault $
 * $Author: yvonnickesnault $
 * $LastChangedDate: 2009-03-29 18:26:37 +0200 (dim., 29 mars 2009) $
 * $LastChangedRevision: 1408 $
 * $LastChangedBy: yvonnickesnault $
 */
class EquipementBonus extends Zend_Db_Table {
	protected $_name = 'equipement_bonus';
	protected $_primary = array('id_equipement_bonus');
	
    function findByIdEquipement($id_equipement) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('equipement_bonus', '*')
		->where('id_equipement_bonus = ?', (int)$id_equipement);
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
			$where .= " $or id_equipement_bonus=".(int)$id;
    	}
    	
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('equipement_bonus', '*')
		->where($where);
		$sql = $select->__toString();
		
		return $db->fetchAll($sql);
    }
}
