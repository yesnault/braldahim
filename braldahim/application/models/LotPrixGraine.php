<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class LotPrixGraine extends Zend_Db_Table {
	protected $_name = 'lot_prix_graine';
	protected $_primary = array("id_fk_type_lot_prix_graine","id_fk_lot_prix_graine");

	function findByIdlot($idlot) {
		
		$liste = "";
		$nomChamp = "id_fk_lot_prix_graine";

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
		$select->from('lot_prix_graine', '*')
		->from('type_graine', '*')
		->where('lot_prix_graine.id_fk_type_lot_prix_graine = type_graine.id_type_graine');

		if ($liste != "") {
			$select->where($nomChamp .'='. $liste);
		} else {
			$select->where('id_fk_lot_prix_graine = ?', intval($idLot));
		}

		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}
