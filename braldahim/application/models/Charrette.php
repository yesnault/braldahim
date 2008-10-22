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
class Charrette extends Zend_Db_Table {
	protected $_name = 'charrette';
	protected $_primary = array('id_charrette');

	function findByIdHobbit($id_hobbit) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('charrette', '*')
		->where('id_fk_hobbit_charrette = '.intval($id_hobbit));
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}
	
	function findByCase($x, $y, $avecProprietaire = true) {
		$and = "";
		if ($avecProprietaire === false) {
			$and = " AND id_fk_hobbit_charrette is null";
		}
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('charrette', '*')
		->where('x_charrette = '.intval($x))
		->where('y_charrette = '.intval($y).$and);
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}
	
	function findByCaseSansProprietaire($x, $y) {
		return findByCase($x, $y, false);
	}
	
	function selectVue($x_min, $y_min, $x_max, $y_max) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('charrette', '*')
		->where('x_charrette <= ?', $x_max)
		->where('x_charrette >= ?', $x_min)
		->where('y_charrette >= ?', $y_min)
		->where('y_charrette <= ?', $y_max)
		->where('id_fk_hobbit_charrette is NULL');
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
	function countByIdHobbit($id_hobbit) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('charrette', 'count(*) as nombre')
		->where('id_fk_hobbit_charrette = '.intval($id_hobbit));
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		$nombre = $resultat[0]["nombre"];
		return $nombre;
	}
	
	function updateCharrette($data) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('charrette', 'quantite_rondin_charrette as quantiteRondin')
		->where('id_fk_hobbit_charrette = ?',$data["id_fk_hobbit_charrette"]);
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		if (count($resultat) == 0) { // insert
			//rien a faire 
		} else { // update
			$quantiteRodin = $resultat[0]["quantiteRondin"];
			if (isset($data["quantite_rondin_charrette"])) {
				$dataUpdate['quantite_rondin_charrette'] = $quantiteRodin + $data["quantite_rondin_charrette"];
			}
			if (isset($dataUpdate)) {
				$where = 'id_fk_hobbit_charrette = '.$data["id_fk_hobbit_charrette"];
				$this->update($dataUpdate, $where);
			}
		}
	}
}
