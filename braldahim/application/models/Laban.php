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
class Laban extends Zend_Db_Table {
	protected $_name = 'laban';
	protected $_primary = array('id_fk_hobbit_laban');

	function findByIdHobbit($id_hobbit) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('laban', '*')
		->where('id_fk_hobbit_laban = '.intval($id_hobbit));
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}

	function insertOrUpdate($data) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('laban', 'count(*) as nombre, 
		quantite_viande_laban as quantiteViande, 
		quantite_peau_laban as quantitePeau, 
		quantite_viande_preparee_laban as quantiteViandePreparee,
		quantite_cuir_laban as quantiteCuir,
		quantite_fourrure_laban as quantiteFourrure,
		quantite_planche_laban as quantitePlanche')
		->where('id_fk_hobbit_laban = ?',$data["id_fk_hobbit_laban"])
		->group(array('quantitePeau', 'quantiteViande', 'quantiteViandePreparee'));
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		if (count($resultat) == 0) { // insert
			$this->insert($data);
		} else { // update
			$nombre = $resultat[0]["nombre"];
			$quantitePeau = $resultat[0]["quantitePeau"];
			$quantiteViande = $resultat[0]["quantiteViande"];
			$quantiteViandePreparee = $resultat[0]["quantiteViandePreparee"];
			$quantiteCuir = $resultat[0]["quantiteCuir"];
			$quantiteFourrure = $resultat[0]["quantiteFourrure"];
			$quantitePlanche = $resultat[0]["quantitePlanche"];
			
			if (isset($data["quantite_viande_laban"])) {
				$dataUpdate['quantite_viande_laban'] = $quantiteViande + $data["quantite_viande_laban"];
			}
			if (isset($data["quantite_peau_laban"])) {
				$dataUpdate['quantite_peau_laban'] = $quantitePeau + $data["quantite_peau_laban"];
			}
			if (isset($data['quantite_viande_preparee_laban'])) {
				$dataUpdate['quantite_viande_preparee_laban'] = $quantiteViandePreparee + $data["quantite_viande_preparee_laban"];
			}
			if (isset($data['quantite_cuir_laban'])) {
				$dataUpdate['quantite_cuir_laban'] = $quantiteCuir + $data["quantite_cuir_laban"];
			}
			if (isset($data['quantite_fourrure_laban'])) {
				$dataUpdate['quantite_fourrure_laban'] = $quantiteFourrure + $data["quantite_fourrure_laban"];
			}
			if (isset($data['quantite_planche_laban'])) {
				$dataUpdate['quantite_planche_laban'] = $quantitePlanche + $data["quantite_planche_laban"];
			}
			if (isset($dataUpdate)) {
				$where = 'id_fk_hobbit_laban = '.$data["id_fk_hobbit_laban"];
				$this->update($dataUpdate, $where);
			}
		}
	}
}
