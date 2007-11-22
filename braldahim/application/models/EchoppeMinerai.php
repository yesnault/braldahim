<?php

class EchoppeMinerai extends Zend_Db_Table {
	protected $_name = 'echoppe_minerai';
	protected $_primary = array('id_fk_echoppe_echoppe_minerai', 'id_fk_type_echoppe_minerai');

	function findByIdEchoppe($id_echoppe) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('echoppe_minerai', '*')
		->from('type_minerai', '*')
		->where('id_fk_echoppe_echoppe_minerai = '.intval($id_echoppe))
		->where('echoppe_minerai.id_fk_type_echoppe_minerai = type_minerai.id_type_minerai');
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}

	function insertOrUpdate($data) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from(
		'echoppe_minerai', 
		'count(*) as nombre, quantite_caisse_echoppe_minerai as quantiteCaisse'
		.', quantite_arriere_echoppe_minerai as quantiteArriere'
		.', quantite_lingots_echoppe_minerai as quantiteLingots')
		->where('id_fk_type_echoppe_minerai = ?',$data["id_fk_type_echoppe_minerai"])
		->where('id_fk_echoppe_echoppe_minerai = ?',$data["id_fk_echoppe_echoppe_minerai"])
		->group('quantite');
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		if (count($resultat) == 0) { // insert
			$this->insert($data);
		} else { // update
			$nombre = $resultat[0]["nombre"];
			$quantiteCaisse = $resultat[0]["quantiteCaisse"];
			$quantiteArriere = $resultat[0]["quantiteArriere"];
			$quantiteLingots = $resultat[0]["quantiteLingots"];
			
			$dataUpdate = array(
			'quantite_caisse_echoppe_minerai' => $quantiteCaisse + $data["quantite_caisse_echoppe_minerai"],
			'quantite_arriere_echoppe_minerai' => $quantiteArriere + $data["quantite_arriere_echoppe_minerai"],
			'quantite_lingots_echoppe_minerai' => $quantiteLingots + $data["quantite_lingots_echoppe_minerai"],
			);
			$where = ' id_fk_type_echoppe_minerai = '.$data["id_fk_type_echoppe_minerai"];
			$where .= ' AND id_fk_echoppe_echoppe_minerai = '.$data["id_fk_echoppe_echoppe_minerai"];
			$this->update($dataUpdate, $where);
		}
	}

}
