<?php

class Laban extends Zend_Db_Table {
	protected $_name = 'laban';
	protected $_primary = array('id_hobbit_laban');

	function findByIdHobbit($id_hobbit) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('laban', '*')
		->where('id_hobbit_laban = '.intval($id_hobbit));
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}

	function insertOrUpdate($data) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('laban', 'count(*) as nombre, 
		quantite_viande_laban as quantiteViande, 
		quantite_peau_laban as quantitePeau, 
		quantite_ration_laban as quantiteRation, 
		quantite_viande_preparee_laban as quantiteViandePreparee')
		->where('id_hobbit_laban = ?',$data["id_hobbit_laban"])
		->group(array('quantitePeau', 'quantiteViande', 'quantiteRation', 'quantiteViandePreparee'));
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		if (count($resultat) == 0) { // insert
			$this->insert($data);
		} else { // update
			$nombre = $resultat[0]["nombre"];
			$quantitePeau = $resultat[0]["quantitePeau"];
			$quantiteViande = $resultat[0]["quantiteViande"];
			$quantiteViandePreparee = $resultat[0]["quantiteViandePreparee"];
			$quantiteRation = $resultat[0]["quantiteRation"];
			
			if (isset($data["quantite_viande_laban"])) {
				$dataUpdate['quantite_viande_laban'] = $quantiteViande + $data["quantite_viande_laban"];
			}
			if (isset($data["quantite_peau_laban"])) {
				$dataUpdate['quantite_peau_laban'] = $quantitePeau + $data["quantite_peau_laban"];
			}
			if (isset($data['quantite_viande_preparee_laban'])) {
				$dataUpdate['quantite_viande_preparee_laban'] = $quantiteViandePreparee + $data["quantite_viande_preparee_laban"];
			}
			if (isset($data['quantite_ration_laban'])) {
				$dataUpdate['quantite_ration_laban'] = $quantiteRation + $data["quantite_ration_laban"];
			}
			if (isset($dataUpdate)) {
				$where = 'id_hobbit_laban = '.$data["id_hobbit_laban"];
				$this->update($dataUpdate, $where);
			}
		}
	}
}
