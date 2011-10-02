<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class LotIngredient extends Zend_Db_Table
{
	protected $_name = 'lot_ingredient';
	protected $_primary = array('id_fk_lot_lot_ingredient', 'id_fk_type_lot_ingredient');

	function findByIdConteneur($idLot)
	{
		return $this->findByIdLot($idLot);
	}

	function findByIdLot($idLot)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('lot_ingredient', '*')
			->from('type_ingredient', '*')
			->where('lot_ingredient.id_fk_type_lot_ingredient = type_ingredient.id_type_ingredient');

		$liste = "";
		$nomChamp = "id_fk_lot_lot_ingredient";

		if (is_array($idLot)) {
			foreach ($idLot as $id) {
				if ((int)$id . "" == $id . "") {
					if ($liste == "") {
						$liste = $id;
					} else {
						$liste = $liste . " OR " . $nomChamp . "=" . $id;
					}
				}
			}
		}

		if ($liste != "") {
			$select->where($nomChamp . '=' . $liste);
		} else {
			$select->where('id_fk_lot_lot_ingredient = ?', intval($idLot));
		}

		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}

	function insertOrUpdate($data)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('lot_ingredient', 'count(*) as nombre,
		quantite_lot_ingredient as quantite')
			->where('id_fk_type_lot_ingredient = ?', $data["id_fk_type_lot_ingredient"])
			->where('id_fk_lot_lot_ingredient = ?', $data["id_fk_lot_lot_ingredient"])
			->group(array('quantite'));
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		if (count($resultat) == 0) { // insert
			$this->insert($data);
		} else { // update
			$nombre = $resultat[0]["nombre"];
			$quantite = $resultat[0]["quantite"];
			$dataUpdate['quantite_lot_ingredient'] = $quantite;

			if (isset($data["quantite_lot_ingredient"])) {
				$dataUpdate['quantite_lot_ingredient'] = $quantite + $data["quantite_lot_ingredient"];
			}

			$where = ' id_fk_type_lot_ingredient = ' . $data["id_fk_type_lot_ingredient"];
			$where .= ' AND id_fk_lot_lot_ingredient = ' . $data["id_fk_lot_lot_ingredient"];

			if ($dataUpdate['quantite_lot_ingredient'] <= 0) { // delete
				$this->delete($where);
			} else { // update
				$this->update($dataUpdate, $where);
			}
		}
	}

}
