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
class Carnet extends Zend_Db_Table {
	protected $_name = 'carnet';
	protected $_primary = array('id_carnet');

	function findByIdBraldunAndIdCarnet($idBraldun, $idCarnet) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('carnet', '*')
		->where('id_fk_braldun_carnet = ?', intval($idBraldun))
		->where('id_carnet = ?', intval($idCarnet));
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}

	function insertOrUpdate($data) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('carnet', 'count(*) as nombre')
		->where('id_carnet = ?',$data["id_carnet"])
		->where('id_fk_braldun_carnet = ?',$data["id_fk_braldun_carnet"]);
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		if (count($resultat) == 1 && $resultat[0]["nombre"] == 0) { // insert
			$this->insert($data);
		} else { // update
			$dataUpdate['texte_carnet'] = $data["texte_carnet"];

			$where = ' id_carnet = '.$data["id_carnet"];
			$where .= ' AND id_fk_braldun_carnet = '.$data["id_fk_braldun_carnet"];
			$this->update($dataUpdate, $where);
		}
	}

}
