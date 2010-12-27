<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Coffre extends Zend_Db_Table {
	protected $_name = 'coffre';
	protected $_primary = array('id_coffre');

	function findByIdCommunaute($id_communaute) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('coffre', '*')
		->where('id_fk_communaute_coffre = ?', intval($id_communaute));
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}

	function findByIdCoffre($idCoffre) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('coffre', '*')
		->where('id_coffre = ?', intval($idCoffre));
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}

	function findByIdBraldun($id_braldun) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('coffre', '*')
		->where('id_fk_braldun_coffre = ?', intval($id_braldun));
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
		->where('id_coffre = ?', $data["id_coffre"])
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
				$where = 'id_coffre = '.$data["id_coffre"];
				$this->update($dataUpdate, $where);
			}
		}
	}
}
