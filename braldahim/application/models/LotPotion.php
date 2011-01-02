<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class LotPotion extends Zend_Db_Table {
	protected $_name = 'lot_potion';
	protected $_primary = array('id_lot_potion');

	function findByIdConteneur($idLot) {
		return $this->findByIdLot($idLot);
	}

	function countByIdConteneur($idLot) {
		return $this->countByIdLot($idLot);
	}

	function countByIdLot($idLot) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('lot_potion', 'count(*) as nombre')
		->where('id_fk_lot_lot_potion = ?', intval($idLot));
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		$nombre = $resultat[0]["nombre"];
		return $nombre;
	}

	function findByIdLot($idLot) {

		$liste = "";
		$nomChamp = "id_fk_lot_lot_potion";

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
		$select->from('lot_potion', '*')
		->from('type_potion')
		->from('type_qualite')
		->from('potion')
		->where('id_lot_potion = id_potion')
		->where('id_fk_type_potion = id_type_potion')
		->where('id_fk_type_qualite_potion = id_type_qualite');

		if ($liste != "") {
			$select->where($nomChamp .'='. $liste);
		} else {
			$select->where('id_fk_lot_lot_potion = ?', intval($idLot));
		}

		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}
