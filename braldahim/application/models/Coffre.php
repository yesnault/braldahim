<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: Coffre.php 595 2008-11-09 11:21:27Z yvonnickesnault $
 * $Author: yvonnickesnault $
 * $LastChangedDate: 2008-11-09 12:21:27 +0100 (Sun, 09 Nov 2008) $
 * $LastChangedRevision: 595 $
 * $LastChangedBy: yvonnickesnault $
 */
class Coffre extends Zend_Db_Table {
	protected $_name = 'coffre';
	protected $_primary = array('id_fk_hobbit_coffre');

	function findByIdHobbit($id_hobbit) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('coffre', '*')
		->where('id_fk_hobbit_coffre = '.intval($id_hobbit));
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}

	function insertOrUpdate($data) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('coffre', 'count(*) as nombre, 
		quantite_viande_coffre as quantiteViande, 
		quantite_peau_coffre as quantitePeau, 
		quantite_ration_coffre as quantiteRation, 
		quantite_viande_preparee_coffre as quantiteViandePreparee,
		quantite_cuir_coffre as quantiteCuir,
		quantite_fourrure_coffre as quantiteFourrure,
		quantite_planche_coffre as quantitePlanche,
		quantite_castar_coffre as quantiteCastar')
		->where('id_fk_hobbit_coffre = ?',$data["id_fk_hobbit_coffre"])
		->group(array('quantitePeau', 'quantiteViande', 'quantiteRation', 'quantiteViandePreparee', 'quantiteCuir', 'quantiteFourrure', 'quantitePlanche', 'quantiteCastar'));
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
			$quantiteCuir = $resultat[0]["quantiteCuir"];
			$quantiteFourrure = $resultat[0]["quantiteFourrure"];
			$quantitePlanche = $resultat[0]["quantitePlanche"];
			$quantiteCastar = $resultat[0]["quantiteCastar"];
			
			if (isset($data["quantite_viande_coffre"])) {
				$dataUpdate['quantite_viande_coffre'] = $quantiteViande + $data["quantite_viande_coffre"];
			}
			if (isset($data["quantite_peau_coffre"])) {
				$dataUpdate['quantite_peau_coffre'] = $quantitePeau + $data["quantite_peau_coffre"];
			}
			if (isset($data['quantite_viande_preparee_coffre'])) {
				$dataUpdate['quantite_viande_preparee_coffre'] = $quantiteViandePreparee + $data["quantite_viande_preparee_coffre"];
			}
			if (isset($data['quantite_ration_coffre'])) {
				$dataUpdate['quantite_ration_coffre'] = $quantiteRation + $data["quantite_ration_coffre"];
			}
			if (isset($data['quantite_cuir_coffre'])) {
				$dataUpdate['quantite_cuir_coffre'] = $quantiteCuir + $data["quantite_cuir_coffre"];
			}
			if (isset($data['quantite_fourrure_coffre'])) {
				$dataUpdate['quantite_fourrure_coffre'] = $quantiteFourrure + $data["quantite_fourrure_coffre"];
			}
			if (isset($data['quantite_planche_coffre'])) {
				$dataUpdate['quantite_planche_coffre'] = $quantitePlanche + $data["quantite_planche_coffre"];
			}
			if (isset($data['quantite_castar_coffre'])) {
				$dataUpdate['quantite_castar_coffre'] = $quantiteCastar + $data["quantite_castar_coffre"];
			}
			if (isset($dataUpdate)) {
				$where = 'id_fk_hobbit_coffre = '.$data["id_fk_hobbit_coffre"];
				$this->update($dataUpdate, $where);
			}
		}
	}
}
