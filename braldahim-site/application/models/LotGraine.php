<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class LotGraine extends Zend_Db_Table
{
	protected $_name = 'lot_graine';
	protected $_primary = array('id_fk_lot_lot_graine', 'id_fk_type_lot_graine');

	function findByIdConteneur($idLot)
	{
		return $this->findByIdLot($idLot);
	}

	function countByIdConteneur($idLot)
	{
		return $this->countByIdLot($idLot);
	}

	function countByIdLot($idLot)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('lot_graine', 'sum(quantite_lot_graine) as nombre')
			->where('id_fk_lot_lot_graine = ?', intval($idLot));
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		$nombre = $resultat[0]["nombre"];
		return $nombre;
	}

	function findByIdLot($idLot)
	{

		$liste = "";
		$nomChamp = "id_fk_lot_lot_graine";

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
		$select->from('lot_graine', '*')
			->from('type_graine', '*')
			->where('lot_graine.id_fk_type_lot_graine = type_graine.id_type_graine');

		if ($liste != "") {
			$select->where($nomChamp . '=' . $liste);
		} else {
			$select->where('id_fk_lot_lot_graine = ?', intval($idLot));
		}

		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}
}
