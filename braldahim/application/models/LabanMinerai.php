<?php

class LabanMinerai extends Zend_Db_Table {
	protected $_name = 'laban_minerai';
	protected $_primary = array('id_fk_hobbit_laban_minerai', 'id_fk_type_laban_minerai');

	function findByIdHobbit($id_hobbit) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('laban_minerai', '*')
		->from('type_minerai', '*')
		->where('id_fk_hobbit_laban_minerai = '.intval($id_hobbit))
		->where('laban_minerai.id_fk_type_laban_minerai = type_minerai.id_type_minerai');
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}

	function insertOrUpdate($data) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('laban_minerai', 'count(*) as nombre, quantite_laban_minerai as quantite')
		->where('id_fk_type_laban_minerai = ?',$data["id_fk_type_laban_minerai"])
		->where('id_fk_hobbit_laban_minerai = ?',$data["id_fk_hobbit_laban_minerai"])
		->group('quantite');
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		if (count($resultat) == 0) { // insert
			$this->insert($data);
		} else { // update
			$nombre = $resultat[0]["nombre"];
			$quantite = $resultat[0]["quantite"];
			
			$dataUpdate = array('quantite_laban_minerai' => $quantite + $data["quantite_laban_minerai"]);
			$where = ' id_fk_type_laban_minerai = '.$data["id_fk_type_laban_minerai"];
			$where .= ' AND id_fk_hobbit_laban_minerai = '.$data["id_fk_hobbit_laban_minerai"];
			
			if ($quantite + $data["quantite_laban_minerai"] = 0) { // delete
				$this->delete($where);
			} else { // update
				$this->update($dataUpdate, $where);
			}

			
		}
	}

}
