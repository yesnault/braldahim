<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class LotTabac extends Zend_Db_Table
{
	protected $_name = 'lot_tabac';
	protected $_primary = array('id_fk_lot_lot_tabac', 'id_fk_type_lot_tabac');

	function findByIdConteneur($idLot)
	{
		return $this->findByIdLot($idLot);
	}

	function findByIdLot($idLot)
	{

		$liste = "";
		$nomChamp = "id_fk_lot_lot_tabac";

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
		$select->from('lot_tabac', '*')
			->from('type_tabac', '*')
			->where('lot_tabac.id_fk_type_lot_tabac = type_tabac.id_type_tabac');

		if ($liste != "") {
			$select->where($nomChamp . '=' . $liste);
		} else {
			$select->where('id_fk_lot_lot_tabac = ?', intval($idLot));
		}

		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}

	function insertOrUpdate($data)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('lot_tabac', 'count(*) as nombre,
		quantite_feuille_lot_tabac as quantiteFeuille')
			->where('id_fk_type_lot_tabac = ?', $data["id_fk_type_lot_tabac"])
			->where('id_fk_lot_lot_tabac = ?', $data["id_fk_lot_lot_tabac"])
			->group(array('quantiteFeuille'));
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		if (count($resultat) == 0) { // insert
			$this->insert($data);
		} else { // update
			$nombre = $resultat[0]["nombre"];
			$quantiteFeuille = $resultat[0]["quantiteFeuille"];

			$dataUpdate['quantite_feuille_lot_tabac'] = $quantiteFeuille;

			if (isset($data["quantite_feuille_lot_tabac"])) {
				$dataUpdate['quantite_feuille_lot_tabac'] = $quantiteFeuille + $data["quantite_feuille_lot_tabac"];
			}

			$where = ' id_fk_type_lot_tabac = ' . $data["id_fk_type_lot_tabac"];
			$where .= ' AND id_fk_lot_lot_tabac = ' . $data["id_fk_lot_lot_tabac"];

			if ($dataUpdate['quantite_feuille_lot_tabac'] <= 0) { // delete
				$this->delete($where);
			} else { // update
				$this->update($dataUpdate, $where);
			}
		}
	}

}
