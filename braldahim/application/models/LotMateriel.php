<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class LotMateriel extends Zend_Db_Table {
	protected $_name = 'lot_materiel';
	protected $_primary = array('id_lot_materiel');

	function findByIdConteneur($idLot) {
		return $this->findByIdLot($idLot);
	}

	function findByIdLot($idLot) {

		$liste = "";
		$nomChamp = "id_fk_lot_lot_materiel";

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
		$select->from('lot_materiel', '*')
		->from('type_materiel', '*')
		->from('materiel', '*')
		->where('id_lot_materiel = id_materiel')
		->where('id_fk_type_materiel = id_type_materiel');

		if ($liste != "") {
			$select->where($nomChamp .'='. $liste);
		} else {
			$select->where('id_fk_lot_lot_materiel = ?', intval($idLot));
		}

		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}
