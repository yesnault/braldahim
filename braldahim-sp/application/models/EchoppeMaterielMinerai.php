<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: EchoppeMaterielMinerai.php 2019 2009-09-19 10:32:13Z yvonnickesnault $
 * $Author: yvonnickesnault $
 * $LastChangedDate: 2009-09-19 12:32:13 +0200 (Sam, 19 sep 2009) $
 * $LastChangedRevision: 2019 $
 * $LastChangedBy: yvonnickesnault $
 */
class EchoppeMaterielMinerai extends Zend_Db_Table {
	protected $_name = 'echoppe_materiel_minerai';
	protected $_primary = array("id_fk_type_echoppe_materiel_minerai","id_fk_echoppe_materiel_minerai");
	
    function findByIdsMateriel($tabId) {
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
			$where .= " $or id_fk_echoppe_materiel_minerai =".(int)$id;
    	}
    	
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('echoppe_materiel_minerai', '*')
		->from('type_minerai', '*')
		->where($where)
		->where('echoppe_materiel_minerai.id_fk_type_echoppe_materiel_minerai = type_minerai.id_type_minerai');
		$sql = $select->__toString();
		
		return $db->fetchAll($sql);
    }
}
