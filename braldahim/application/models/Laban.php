<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Laban extends Zend_Db_Table {
	protected $_name = 'laban';
	protected $_primary = array('id_fk_braldun_laban');

	function findByIdBraldun($id_braldun) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('laban', '*')
		->where('id_fk_braldun_laban = '.intval($id_braldun));
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}

	function insertOrUpdate($data) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('laban', 'count(*) as nombre,
		quantite_peau_laban as quantitePeau, 
		quantite_cuir_laban as quantiteCuir,
		quantite_fourrure_laban as quantiteFourrure,
		quantite_planche_laban as quantitePlanche,
		quantite_rondin_laban as quantiteRondin')
		->where('id_fk_braldun_laban = ?',$data["id_fk_braldun_laban"])
		->group(array('quantitePeau', 'quantiteCuir', 'quantiteFourrure', 'quantitePlanche', 'quantiteRondin'));
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
				
			if (isset($data["quantite_peau_laban"])) {
				$dataUpdate['quantite_peau_laban'] = $quantitePeau + $data["quantite_peau_laban"];
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
			if (isset($data['quantite_rondin_laban'])) {
				$dataUpdate['quantite_rondin_laban'] = $quantiteRondin + $data["quantite_rondin_laban"];
			}
			if (isset($dataUpdate)) {
				$where = 'id_fk_braldun_laban = '.$data["id_fk_braldun_laban"];
				$this->update($dataUpdate, $where);
			}
		}
	}
}
