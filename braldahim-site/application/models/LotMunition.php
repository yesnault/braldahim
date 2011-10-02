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
}
