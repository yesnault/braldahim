<?php

class EchoppeEquipementMinerai extends Zend_Db_Table {
	protected $_name = 'echoppe_equipement_minerai';
	protected $_primary = array("id_fk_type_echoppe_equipement_minerai","id_fk_echoppe_equipement_minerai");
	
	function insertOrUpdate($data) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from(
		'echoppe_equipement_minerai', 
		'count(*) as nombre, prix_echoppe_equipement_minerai as prix')
		->where('id_fk_type_echoppe_equipement_minerai = ?',$data["id_fk_type_echoppe_equipement_minerai"])
		->where('id_fk_echoppe_equipement_minerai = ?',$data["id_fk_echoppe_equipement_minerai"])
		->group(array('prix'));
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		if (count($resultat) == 0) { // insert
			$this->insert($data);
		} else { // update
			$nombre = $resultat[0]["nombre"];
			$prix = $resultat[0]["prix"];
			
			$prix = $prix + $data["prix_echoppe_equipement_minerai"];
			if ($prix < 0) $prix = 0;
			
			$dataUpdate = array(
			'prix_echoppe_equipement_minerai' => $prix,
			);
			$where = ' id_fk_type_echoppe_equipement_minerai = '.$data["id_fk_type_echoppe_equipement_minerai"];
			$where .= ' AND id_fk_echoppe_equipement_minerai = '.$data["id_fk_echoppe_equipement_minerai"];
			$this->update($dataUpdate, $where);
		}
	}
}
