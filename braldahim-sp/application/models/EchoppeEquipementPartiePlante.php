<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: EchoppeEquipementPartiePlante.php 595 2008-11-09 11:21:27Z yvonnickesnault $
 * $Author: yvonnickesnault $
 * $LastChangedDate: 2008-11-09 12:21:27 +0100 (Dim, 09 nov 2008) $
 * $LastChangedRevision: 595 $
 * $LastChangedBy: yvonnickesnault $
 */
class EchoppeEquipementPartiePlante extends Zend_Db_Table {
	protected $_name = 'echoppe_equipement_partieplante';
	protected $_primary = array("id_fk_type_echoppe_equipement_partieplante","id_fk_type_plante_echoppe_equipement_partieplante", "id_fk_echoppe_equipement_partieplante");

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
			$where .= " $or id_fk_echoppe_equipement_partieplante =".(int)$id;
    	}
    	
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('echoppe_equipement_partieplante', '*')
		->from('type_partieplante', '*')
		->from('type_plante', '*')
		->where($where)
		->where('echoppe_equipement_partieplante.id_fk_type_echoppe_equipement_partieplante = type_partieplante.id_type_partieplante')
		->where('echoppe_equipement_partieplante.id_fk_type_plante_echoppe_equipement_partieplante = type_plante.id_type_plante');
		$sql = $select->__toString();
		
		return $db->fetchAll($sql);
    }
}
