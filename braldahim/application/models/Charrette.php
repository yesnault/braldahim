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
class Charrette extends Zend_Db_Table {
	protected $_name = 'charrette';
	protected $_primary = array('id_charrette');

	function findByIdHobbit($idHobbit) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('charrette', '*')
		->from('type_materiel', '*')
		->from('materiel', '*')
		->where('id_charrette = id_materiel')
		->where('id_fk_type_materiel = id_type_materiel')
		->where('id_fk_hobbit_charrette = '.intval($idHobbit));
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}
	
	function findByIdCharrette($idCharrette) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('charrette', '*')
		->from('type_materiel', '*')
		->from('materiel', '*')
		->where('id_charrette = id_materiel')
		->where('id_fk_type_materiel = id_type_materiel')
		->where('id_charrette = '.intval($idCharrette));
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}
	
	function findByPositionAvecHobbit($x, $y, $z) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('charrette', '*')
		->from('type_materiel', '*')
		->from('hobbit', '*')
		->from('materiel', '*')
		->where('id_hobbit = id_fk_hobbit_charrette')
		->where('id_charrette = id_materiel')
		->where('id_fk_type_materiel = id_type_materiel')
		->where('est_ko_hobbit = ?', 'non')
		->where('x_hobbit = ?', intval($x))
		->where('y_hobbit = ?', intval($y))
		->where('z_hobbit = ?', intval($z));
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}

	function findByCase($x, $y, $z, $avecProprietaire = true) {
		$and = "";
		if ($avecProprietaire === false) {
			$and = " AND id_fk_hobbit_charrette is null";
		}
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('charrette', '*')
		->from('type_materiel', '*')
		->from('materiel', '*')
		->where('id_charrette = id_materiel')
		->where('id_fk_type_materiel = id_type_materiel')
		->where('z_charrette = '.intval($z))
		->where('x_charrette = '.intval($x))
		->where('y_charrette = '.intval($y).$and);
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}

	function findByCaseSansProprietaire($x, $y, $z) {
		return findByCase($x, $y, $z, false);
	}

	function selectVue($x_min, $y_min, $x_max, $y_max, $z) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('charrette', '*')
		->from('type_materiel', '*')
		->from('materiel', '*')
		->where('x_charrette <= ?', $x_max)
		->where('x_charrette >= ?', $x_min)
		->where('y_charrette >= ?', $y_min)
		->where('y_charrette <= ?', $y_max)
		->where('z_charrette = ?', $z)
		->where('id_charrette = id_materiel')
		->where('id_fk_type_materiel = id_type_materiel')
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
		$select->from('charrette', 'quantite_rondin_charrette as quantiteRondin');
		
		if (isset($data["id_charrette"])) {
			$select->where('id_charrette = ?',$data["id_charrette"]);
		} else {
			$select->where('id_fk_hobbit_charrette = ?',$data["id_fk_hobbit_charrette"]);
		}
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

	function insertOrUpdate($data) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('charrette', 'count(*) as nombre,
		quantite_castar_charrette as quantiteCastar, 
		quantite_peau_charrette as quantitePeau, 
		quantite_cuir_charrette as quantiteCuir,
		quantite_fourrure_charrette as quantiteFourrure,
		quantite_planche_charrette as quantitePlanche,
		quantite_rondin_charrette as quantiteRondin');	
		if (isset($data["id_charrette"])) {
			$select->where('id_charrette = ?',$data["id_charrette"]);
		} else {
			$select->where('id_fk_hobbit_charrette = ?',$data["id_fk_hobbit_charrette"]);
		}
		$select->group(array('quantitePeau', 'quantiteCastar', 'quantiteCuir', 'quantiteFourrure', 'quantitePlanche', 'quantiteRondin'));
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		if (count($resultat) == 0) { // insert
			$this->insert($data);
		} else { // update
			$nombre = $resultat[0]["nombre"];
			$quantiteCastar = $resultat[0]["quantiteCastar"];
			$quantitePeau = $resultat[0]["quantitePeau"];
			$quantiteCuir = $resultat[0]["quantiteCuir"];
			$quantiteFourrure = $resultat[0]["quantiteFourrure"];
			$quantitePlanche = $resultat[0]["quantitePlanche"];
			$quantiteRondin = $resultat[0]["quantiteRondin"];

			if (isset($data["quantite_castar_charrette"])) {
				$dataUpdate['quantite_castar_charrette'] = $quantiteCastar + $data["quantite_castar_charrette"];
			}
			if (isset($data["quantite_peau_charrette"])) {
				$dataUpdate['quantite_peau_charrette'] = $quantitePeau + $data["quantite_peau_charrette"];
			}
			if (isset($data['quantite_cuir_charrette'])) {
				$dataUpdate['quantite_cuir_charrette'] = $quantiteCuir + $data["quantite_cuir_charrette"];
			}
			if (isset($data['quantite_fourrure_charrette'])) {
				$dataUpdate['quantite_fourrure_charrette'] = $quantiteFourrure + $data["quantite_fourrure_charrette"];
			}
			if (isset($data['quantite_planche_charrette'])) {
				$dataUpdate['quantite_planche_charrette'] = $quantitePlanche + $data["quantite_planche_charrette"];
			}
			if (isset($data["quantite_rondin_charrette"])) {
				$dataUpdate['quantite_rondin_charrette'] = $quantiteRondin + $data["quantite_rondin_charrette"];
			}
			if (isset($dataUpdate)) {
				if (isset($data["id_charrette"])) {
					$where = 'id_charrette = '.$data["id_charrette"];
				} else {
					$where = 'id_fk_hobbit_charrette = '.$data["id_fk_hobbit_charrette"];
				}
				$this->update($dataUpdate, $where);
			}
		}
	}
}
