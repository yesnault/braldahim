<?php

class Charrette extends Zend_Db_Table {
	protected $_name = 'charrette';
	protected $_primary = array('id_charrette');

	function findByIdHobbit($id_hobbit) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('charrette', '*')
		->where('id_hobbit_charrette = '.intval($id_hobbit));
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}
	
	function countByIdHobbit($id_hobbit) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('charrette', 'count(*) as nombre')
		->where('id_hobbit_charrette = '.intval($id_hobbit));
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		$nombre = $resultat[0]["nombre"];
		return $nombre;
	}
	
	function updateCharrette($data) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('charrette', 'quantite_rondin_charrette as quantiteRondin')
		->where('id_hobbit_charrette = ?',$data["id_hobbit_charrette"]);
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
				$where = 'id_hobbit_charrette = '.$data["id_hobbit_charrette"];
				$this->update($dataUpdate, $where);
			}
		}
	}
}
