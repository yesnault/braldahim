<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class LotPrixMinerai extends Zend_Db_Table {
	protected $_name = 'lot_prix_minerai';
	protected $_primary = array("id_fk_type_lot_prix_minerai","id_fk_lot_prix_minerai");

	function findByIdLot($idLot) {
			
		$liste = "";
		$nomChamp = "id_fk_lot_prix_minerai";

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
		$select->from('lot_prix_minerai', '*')
		->from('type_minerai', '*')
		->where('lot_prix_minerai.id_fk_type_lot_prix_minerai = type_minerai.id_type_minerai');
		$sql = $select->__toString();

		if ($liste != "") {
			$select->where($nomChamp .'='. $liste);
		} else {
			$select->where('id_fk_lot_prix_minerai = ?', intval($idLot));
		}

		return $db->fetchAll($sql);
	}
}
