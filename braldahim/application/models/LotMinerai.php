<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class LotMinerai extends Zend_Db_Table
{
	protected $_name = 'lot_minerai';
	protected $_primary = array('id_fk_lot_lot_minerai', 'id_fk_type_lot_minerai');

	function findByIdConteneur($idLot)
	{
		return $this->findByIdLot($idLot);
	}

	function findByIdLot($idLot)
	{

		$liste = "";
		$nomChamp = "id_fk_lot_lot_minerai";

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
		$select->from('lot_minerai', '*')
			->from('type_minerai', '*')
			->where('lot_minerai.id_fk_type_lot_minerai = type_minerai.id_type_minerai');

		if ($liste != "") {
			$select->where($nomChamp . '=' . $liste);
		} else {
			$select->where('id_fk_lot_lot_minerai = ?', intval($idLot));
		}

		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}

	function insertOrUpdate($data)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('lot_minerai', 'count(*) as nombre,
		quantite_brut_lot_minerai as quantiteBrut, 
		quantite_lingots_lot_minerai as quantiteLingots')
			->where('id_fk_type_lot_minerai = ?', $data["id_fk_type_lot_minerai"])
			->where('id_fk_lot_lot_minerai = ?', $data["id_fk_lot_lot_minerai"])
			->group(array('quantiteBrut', 'quantiteLingots'));
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		if (count($resultat) == 0) { // insert
			$this->insert($data);
		} else { // update
			$nombre = $resultat[0]["nombre"];
			$quantiteBrut = $resultat[0]["quantiteBrut"];
			$quantiteLingots = $resultat[0]["quantiteLingots"];

			$dataUpdate['quantite_brut_lot_minerai'] = $quantiteBrut;
			$dataUpdate['quantite_lingots_lot_minerai'] = $quantiteLingots;

			if (isset($data["quantite_brut_lot_minerai"])) {
				$dataUpdate['quantite_brut_lot_minerai'] = $quantiteBrut + $data["quantite_brut_lot_minerai"];
			}
			if (isset($data["quantite_lingots_lot_minerai"])) {
				$dataUpdate['quantite_lingots_lot_minerai'] = $quantiteLingots + $data["quantite_lingots_lot_minerai"];
			}

			$where = ' id_fk_type_lot_minerai = ' . $data["id_fk_type_lot_minerai"];
			$where .= ' AND id_fk_lot_lot_minerai = ' . $data["id_fk_lot_lot_minerai"];

			if ($dataUpdate['quantite_brut_lot_minerai'] <= 0 && $dataUpdate['quantite_lingots_lot_minerai'] <= 0) { // delete
				$this->delete($where);
			} else { // update
				$this->update($dataUpdate, $where);
			}
		}
	}

}
