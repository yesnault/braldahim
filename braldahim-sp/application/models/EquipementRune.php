<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: EquipementRune.php 1974 2009-09-03 10:28:09Z yvonnickesnault $
 * $Author: yvonnickesnault $
 * $LastChangedDate: 2009-09-03 12:28:09 +0200 (jeu., 03 sept. 2009) $
 * $LastChangedRevision: 1974 $
 * $LastChangedBy: yvonnickesnault $
 */
class EquipementRune extends Zend_Db_Table {
	protected $_name = 'equipement_rune';
	protected $_primary = array('id_equipement_rune', 'id_rune_equipement_rune');
	
    function findByIdEquipement($id_equipement) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('equipement_rune', '*')
		->from('type_rune', '*')
		->from('rune', '*')
		->where('id_rune_equipement_rune = id_rune')
		->where('id_equipement_rune = ?', (int)$id_equipement)
		->where('id_fk_type_rune = id_type_rune')
		->order('ordre_equipement_rune');
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
			$where .= " $or id_equipement_rune=".(int)$id;
    	}
    	
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('equipement_rune', '*')
		->from('type_rune', '*')
		->from('rune', '*')
		->where('id_rune_equipement_rune = id_rune')
		->where($where)
		->where('id_fk_type_rune = id_type_rune')
		->order('ordre_equipement_rune');
		$sql = $select->__toString();
		
		return $db->fetchAll($sql);
    }
}
