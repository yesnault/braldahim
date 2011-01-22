<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class LotPartieplante extends Zend_Db_Table {
	protected $_name = 'lot_partieplante';
	protected $_primary = array('id_fk_type_lot_partieplante', 'id_fk_lot_lot_partieplante');

	function findByIdConteneur($idLot) {
		return $this->findByIdLot($idLot);
	}

	function findByIdLot($idLot) {

		$liste = "";
		$nomChamp = "id_fk_lot_lot_partieplante";

		if (is_array($idLot)) {
			foreach($idLot as $id) {
				if ((int) $id."" == $id."") {
					if ($liste == "") {
						$liste = $id;
					} else {
						$liste = $liste." OR ".$nomChamp."=".$id;
					}
				}
			}
		}

		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('lot_partieplante', '*')
		->from('type_partieplante', '*')
		->from('type_plante', '*')
		->where('lot_partieplante.id_fk_type_lot_partieplante = type_partieplante.id_type_partieplante')
		->where('lot_partieplante.id_fk_type_plante_lot_partieplante = type_plante.id_type_plante')
		->order(array('nom_type_plante', 'nom_type_partieplante'));

		if ($liste != "") {
			$select->where($nomChamp .'='. $liste);
		} else {
			$select->where('id_fk_lot_lot_partieplante = ?', intval($idLot));
		}

		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}

	function insertOrUpdate($data) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('lot_partieplante', 'count(*) as nombre, quantite_lot_partieplante as quantiteBrute,  quantite_preparee_lot_partieplante as quantitePreparee')
		->where('id_fk_type_lot_partieplante = ?',$data["id_fk_type_lot_partieplante"])
		->where('id_fk_lot_lot_partieplante = ?',$data["id_fk_lot_lot_partieplante"])
		->where('id_fk_type_plante_lot_partieplante = ?',$data["id_fk_type_plante_lot_partieplante"])
		->group(array('quantiteBrute', 'quantitePreparee'));
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		if (count($resultat) == 0) { // insert
			$this->insert($data);
		} else { // update
			$nombre = $resultat[0]["nombre"];
			$quantiteBrute = $resultat[0]["quantiteBrute"];
			$quantitePreparee = $resultat[0]["quantitePreparee"];

			$dataUpdate['quantite_lot_partieplante']  = $quantiteBrute;
			$dataUpdate['quantite_preparee_lot_partieplante']  = $quantitePreparee;

			if (isset($data["quantite_lot_partieplante"])) {
				$quantiteBrute += $data["quantite_lot_partieplante"];
			};

			if (isset($data["quantite_preparee_lot_partieplante"])) {
				$quantitePreparee += $data["quantite_preparee_lot_partieplante"];
			};

			$dataUpdate = array(
					'quantite_lot_partieplante' => $quantiteBrute,
					'quantite_preparee_lot_partieplante' => $quantitePreparee,
			);

			$where = ' id_fk_type_lot_partieplante = '.$data["id_fk_type_lot_partieplante"];
			$where .= ' AND id_fk_lot_lot_partieplante = '.$data["id_fk_lot_lot_partieplante"];
			$where .= ' AND id_fk_type_plante_lot_partieplante = '.$data["id_fk_type_plante_lot_partieplante"];
			$this->update($dataUpdate, $where);
		}
	}
}
