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
class EchoppeEquipementMinerai extends Zend_Db_Table {
	protected $_name = 'echoppe_equipement_minerai';
	protected $_primary = array("id_fk_type_echoppe_equipement_minerai","id_fk_echoppe_equipement_minerai");
	
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
			$where .= " $or id_fk_echoppe_equipement_minerai =".(int)$id;
    	}
    	
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('echoppe_equipement_minerai', '*')
		->from('type_minerai', '*')
		->where($where)
		->where('echoppe_equipement_minerai.id_fk_type_echoppe_equipement_minerai = type_minerai.id_type_minerai');
		$sql = $select->__toString();
		
		return $db->fetchAll($sql);
    }
}
