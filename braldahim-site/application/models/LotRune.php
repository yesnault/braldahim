<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class LotRune extends Zend_Db_Table
{
	protected $_name = 'lot_rune';
	protected $_primary = array('id_rune_lot_rune');

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
		$select->from('lot_rune', 'count(*) as nombre')
			->where('id_fk_lot_lot_rune = ?', intval($idLot));
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		$nombre = $resultat[0]["nombre"];
		return $nombre;
	}

	function findByIdLot($idLot, $identifiee = null)
	{

		$liste = "";
		$nomChamp = "id_fk_lot_lot_rune";

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

		$whereIdentifiee = "";
		if ($identifiee != null) {
			$whereIdentifiee = " AND est_identifiee_rune = '" . $identifiee . "'";
		}
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('lot_rune', '*')
			->from('type_rune', '*')
			->from('rune', '*')
			->where('id_rune_lot_rune = id_rune')
			->where('id_fk_type_rune = id_type_rune' . $whereIdentifiee);

		if ($liste != "") {
			$select->where($nomChamp . '=' . $liste);
		} else {
			$select->where('id_fk_lot_lot_rune = ?', intval($idLot));
		}

		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}
}
