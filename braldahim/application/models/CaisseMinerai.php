<?php

class CaisseMinerai extends Zend_Db_Table {
	protected $_name = 'caisse_minerai';
	protected $_primary = array('id_fk_echoppe_caisse_minerai', 'id_fk_type_caisse_minerai');

	function findByIdEchoppe($id_fk_echoppe) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('caisse_minerai', '*')
		->from('type_minerai', '*')
		->where('id_fk_echoppe_caisse_minerai = '.intval($id_fk_echoppe))
		->where('caisse_minerai.id_fk_type_caisse_minerai = type_minerai.id_type_minerai');
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}

	function insertOrUpdate($data) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('caisse_minerai', 'count(*) as nombre, quantite_caisse_minerai as quantite')
		->where('id_fk_type_caisse_minerai = ?',$data["id_fk_type_caisse_minerai"])
		->where('id_fk_echoppe_caisse_minerai = ?',$data["id_fk_echoppe_caisse_minerai"])
		->group('quantite');
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		if (count($resultat) == 0) { // insert
			$this->insert($data);
		} else { // update
			$nombre = $resultat[0]["nombre"];
			$quantite = $resultat[0]["quantite"];
			$dataUpdate = array('quantite_caisse_minerai' => $quantite + $data["quantite_caisse_minerai"]);
			$where = ' id_fk_type_caisse_minerai = '.$data["id_fk_type_caisse_minerai"];
			$where .= ' AND id_fk_echoppe_caisse_minerai = '.$data["id_fk_echoppe_caisse_minerai"];
			$this->update($dataUpdate, $where);
		}
	}

}
