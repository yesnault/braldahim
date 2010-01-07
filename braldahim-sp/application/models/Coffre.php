<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: Coffre.php 2211 2009-12-06 14:12:07Z yvonnickesnault $
 * $Author: yvonnickesnault $
 * $LastChangedDate: 2009-12-06 15:12:07 +0100 (dim., 06 déc. 2009) $
 * $LastChangedRevision: 2211 $
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
		quantite_peau_coffre as quantitePeau, 
		quantite_cuir_coffre as quantiteCuir,
		quantite_fourrure_coffre as quantiteFourrure,
		quantite_planche_coffre as quantitePlanche,
		quantite_rondin_coffre as quantiteRondin,
		quantite_castar_coffre as quantiteCastar')
		->where('id_fk_hobbit_coffre = ?',$data["id_fk_hobbit_coffre"])
		->group(array('quantitePeau', 'quantiteCuir', 'quantiteFourrure', 'quantitePlanche', 'quantiteRondin', 'quantiteCastar'));
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		if (count($resultat) == 0) { // insert
			$this->insert($data);
		} else { // update
			$nombre = $resultat[0]["nombre"];
			$quantitePeau = $resultat[0]["quantitePeau"];
			$quantiteCuir = $resultat[0]["quantiteCuir"];
			$quantiteFourrure = $resultat[0]["quantiteFourrure"];
			$quantitePlanche = $resultat[0]["quantitePlanche"];
			$quantiteRondin = $resultat[0]["quantiteRondin"];
			$quantiteCastar = $resultat[0]["quantiteCastar"];
				
			if (isset($data["quantite_peau_coffre"])) {
				$dataUpdate['quantite_peau_coffre'] = $quantitePeau + $data["quantite_peau_coffre"];
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
			if (isset($data['quantite_rondin_coffre'])) {
				$dataUpdate['quantite_rondin_coffre'] = $quantiteRondin + $data["quantite_rondin_coffre"];
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
