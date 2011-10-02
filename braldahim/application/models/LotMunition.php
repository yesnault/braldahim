<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class LotMunition extends Zend_Db_Table
{
	protected $_name = 'lot_munition';
	protected $_primary = array('id_fk_lot_lot_munition', 'id_fk_type_lot_munition');

	function findByIdConteneur($idLot)
	{
		return $this->findByIdLot($idLot);
	}

	function findByIdLot($idLot)
	{

		$liste = "";
		$nomChamp = "id_fk_lot_lot_munition";

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

		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('lot_munition', '*')
			->from('type_munition', '*')
			->where('lot_munition.id_fk_type_lot_munition = type_munition.id_type_munition');

		if ($liste != "") {
			$select->where($nomChamp . '=' . $liste);
		} else {
			$select->where('id_fk_lot_lot_munition = ?', intval($idLot));
		}

		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}

	function insertOrUpdate($data)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('lot_munition', 'count(*) as nombre,
		quantite_lot_munition as quantite')
			->where('id_fk_type_lot_munition = ?', $data["id_fk_type_lot_munition"])
			->where('id_fk_lot_lot_munition = ?', $data["id_fk_lot_lot_munition"])
			->group(array('quantite'));
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		if (count($resultat) == 0) { // insert
			$this->insert($data);
		} else { // update
			$nombre = $resultat[0]["nombre"];
			$quantite = $resultat[0]["quantite"];

			$dataUpdate['quantite_lot_munition'] = $quantite;

			if (isset($data["quantite_lot_munition"])) {
				$dataUpdate['quantite_lot_munition'] = $quantite + $data["quantite_lot_munition"];
			}

			$where = ' id_fk_type_lot_munition = ' . $data["id_fk_type_lot_munition"];
			$where .= ' AND id_fk_lot_lot_munition = ' . $data["id_fk_lot_lot_munition"];

			if ($dataUpdate['quantite_lot_munition'] <= 0) { // delete
				$this->delete($where);
			} else { // update
				$this->update($dataUpdate, $where);
			}
		}
	}
}
