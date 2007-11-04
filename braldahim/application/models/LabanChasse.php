<?php

class LabanChasse extends Zend_Db_Table {
	protected $_name = 'laban_chasse';
	protected $_primary = array('id_hobbit_laban_chasse');

	function findByIdHobbit($id_hobbit) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('laban_chasse', '*')
		->where('id_hobbit_laban_chasse = '.intval($id_hobbit));
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}

	function insertOrUpdate($data) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('laban_chasse', 'count(*) as nombre, quantite_viande_laban_chasse as quantiteViande, quantite_peau_laban_chasse as quantitePeau, quantite_fourrure_laban_chasse as quantiteFourrure')
		->where('id_hobbit_laban_chasse = ?',$data["id_hobbit_laban_chasse"])
		->group(array('quantitePeau', 'quantiteFourrure', 'quantiteViande'));
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		if (count($resultat) == 0) { // insert
			$this->insert($data);
		} else { // update
			$nombre = $resultat[0]["nombre"];
			$quantitePeau = $resultat[0]["quantitePeau"];
			$quantiteViande = $resultat[0]["quantiteViande"];
			$quantiteFourrure = $resultat[0]["quantiteFourrure"];
			$dataUpdate = array(
			'quantite_viande_laban_chasse' => $quantiteViande + $data["quantite_viande_laban_chasse"],
			'quantite_peau_laban_chasse' => $quantitePeau + $data["quantite_peau_laban_chasse"],
			'quantite_fourrure_laban_chasse' => $quantiteFourrure + $data["quantite_fourrure_laban_chasse"],
			);
			$where = 'id_hobbit_laban_chasse = '.$data["id_hobbit_laban_chasse"];
			$this->update($dataUpdate, $where);
		}
	}

}
