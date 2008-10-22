<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id:$
 * $Author:$
 * $LastChangedDate:$
 * $LastChangedRevision:$
 * $LastChangedBy:$
 */
class EchoppePotionMinerai extends Zend_Db_Table {
	protected $_name = 'echoppe_potion_minerai';
	protected $_primary = array("id_fk_type_echoppe_potion_minerai","id_fk_echoppe_potion_minerai");
	
	function insertOrUpdate($data) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from(
		'echoppe_potion_minerai', 
		'count(*) as nombre, prix_echoppe_potion_minerai as prix')
		->where('id_fk_type_echoppe_potion_minerai = ?',$data["id_fk_type_echoppe_potion_minerai"])
		->where('id_fk_echoppe_potion_minerai = ?',$data["id_fk_echoppe_potion_minerai"])
		->group(array('prix'));
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		if (count($resultat) == 0) { // insert
			$this->insert($data);
		} else { // update
			$nombre = $resultat[0]["nombre"];
			$prix = $resultat[0]["prix"];
			
			$prix = $prix + $data["prix_echoppe_potion_minerai"];
			if ($prix < 0) $prix = 0;
			
			$dataUpdate = array(
			'prix_echoppe_potion_minerai' => $prix,
			);
			$where = ' id_fk_type_echoppe_potion_minerai = '.$data["id_fk_type_echoppe_potion_minerai"];
			$where .= ' AND id_fk_echoppe_potion_minerai = '.$data["id_fk_echoppe_potion_minerai"];
			$this->update($dataUpdate, $where);
		}
	}
	
   function findByIdsPotion($tabId) {
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
			$where .= " $or id_fk_echoppe_potion_minerai =".(int)$id;
    	}
    	
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('echoppe_potion_minerai', '*')
		->from('type_minerai', '*')
		->where($where)
		->where('echoppe_potion_minerai.id_fk_type_echoppe_potion_minerai = type_minerai.id_type_minerai');
		$sql = $select->__toString();
		
		return $db->fetchAll($sql);
    }
}
