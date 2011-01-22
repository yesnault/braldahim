<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class LabanGraine extends Zend_Db_Table {
	protected $_name = 'laban_graine';
	protected $_primary = array('id_fk_braldun_laban_graine', 'id_fk_type_laban_graine');

	function findByIdConteneur($idBraldun) {
		return $this->findByIdBraldun($idBraldun);
	}

	function countByIdConteneur($idBraldun) {
		return $this->countByIdBraldun($idBraldun);
	}

	function findByIdBraldun($idBraldun) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('laban_graine', '*')
		->from('type_graine', '*')
		->where('id_fk_braldun_laban_graine = ?', intval($idBraldun))
		->where('laban_graine.id_fk_type_laban_graine = type_graine.id_type_graine');
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}

	function countByIdBraldun($idBraldun) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('laban_graine', 'sum(quantite_laban_graine) as nombre')
		->where('id_fk_braldun_laban_graine = ?', intval($idBraldun));
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		$nombre = $resultat[0]["nombre"];
		return $nombre;
	}

	function insertOrUpdate($data) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('laban_graine', 'count(*) as nombre, quantite_laban_graine as quantite')
		->where('id_fk_type_laban_graine = ?',$data["id_fk_type_laban_graine"])
		->where('id_fk_braldun_laban_graine = ?',$data["id_fk_braldun_laban_graine"])
		->group(array('quantite'));
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		if (count($resultat) == 0) { // insert
			$this->insert($data);
		} else { // update
			$nombre = $resultat[0]["nombre"];
			$quantite = $resultat[0]["quantite"];

			$dataUpdate['quantite_laban_graine']  = $quantite;

			if (isset($data["quantite_laban_graine"])) {
				$dataUpdate['quantite_laban_graine'] = $quantite + $data["quantite_laban_graine"];
			}

			$where = ' id_fk_type_laban_graine = '.$data["id_fk_type_laban_graine"];
			$where .= ' AND id_fk_braldun_laban_graine = '.$data["id_fk_braldun_laban_graine"];

			if ($dataUpdate['quantite_laban_graine'] <= 0) { // delete
				$this->delete($where);
			} else { // update
				$this->update($dataUpdate, $where);
			}
		}
	}

}
