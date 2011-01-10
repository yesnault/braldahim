<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class EchoppeGraine extends Zend_Db_Table {
	protected $_name = 'echoppe_graine';
	protected $_primary = array('id_fk_echoppe_echoppe_graine', 'id_fk_type_echoppe_graine');

	function findByIdEchoppe($idEchoppe) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('echoppe_graine', '*')
		->from('type_graine', '*')
		->where('id_fk_echoppe_echoppe_graine = ?', intval($idEchoppe))
		->where('echoppe_graine.id_fk_type_echoppe_graine = type_graine.id_type_graine');
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}

	function insertOrUpdate($data) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from(
		'echoppe_graine',  'count(*) as nombre,
		quantite_arriere_echoppe_graine as quantiteArriere')
		->where('id_fk_type_echoppe_graine = ?',$data["id_fk_type_echoppe_graine"])
		->where('id_fk_echoppe_echoppe_graine = ?',$data["id_fk_echoppe_echoppe_graine"])
		->group(array('quantiteArriere'));
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		if (count($resultat) == 0) { // insert
			$this->insert($data);
		} else { // update
			$nombre = $resultat[0]["nombre"];
			$quantiteArriere = $resultat[0]["quantiteArriere"];
			
			if (isset($data["quantite_arriere_echoppe_graine"])) {
				$quantiteArriere = $quantiteArriere + $data["quantite_arriere_echoppe_graine"];
			}
			
			if ($quantiteArriere < 0) $quantiteArriere = 0;
			
			$dataUpdate = array(
				'quantite_arriere_echoppe_graine' => $quantiteArriere,
			);
			$where = ' id_fk_type_echoppe_graine = '.$data["id_fk_type_echoppe_graine"];
			$where .= ' AND id_fk_echoppe_echoppe_graine = '.$data["id_fk_echoppe_echoppe_graine"];
			
			if ($quantiteArriere == 0) { // delete
				$this->delete($where);
			} else { // update
				$this->update($dataUpdate, $where);
			}
			
			
		}
	}

}
