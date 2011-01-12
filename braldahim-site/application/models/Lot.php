<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Lot extends Zend_Db_Table {
	protected $_name = 'lot';
	protected $_primary = array('id_lot');

	function findByIdConteneur($idLot) {
		return $this->findByIdLot($idLot);
	}

	function findByEtals($idRegion = null) {
		Zend_Loader::loadClass('TypeLot');
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('lot', '*')
		->from('echoppe', '*')
		->where('id_fk_echoppe_lot is not null')
		->where('id_fk_type_lot = ?', TypeLot::ID_TYPE_VENTE_ECHOPPE_TOUS)
		->where('id_fk_echoppe_lot = id_echoppe');
		
		if ($idRegion != -1 && $idRegion != null) {
			$select->from('region');
			$select->where('region.x_min_region <= echoppe.x_echoppe');
			$select->where('region.x_max_region >= echoppe.x_echoppe');
			$select->where('region.y_min_region <= echoppe.y_echoppe');
			$select->where('region.y_max_region >= echoppe.y_echoppe');
			$select->where('region.id_region = ?', $idRegion);
		}

		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findByIdEchoppe($idEchoppe, $idLot = null) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('lot', '*')
		->where('id_fk_echoppe_lot = ?', intval($idEchoppe));

		if ($idLot != null) {
			$select->where('id_lot = ?', intval($idLot));
		}

		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findByHotel($perime = false) {
		Zend_Loader::loadClass("TypeLot");
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('lot', '*')
		->where('id_fk_type_lot = ?', TypeLot::ID_TYPE_VENTE_HOTEL);

		if ($perime === true) {
			$dateFin = date('Y-m-d H:i:s');
			$select->where('date_fin_lot <= ?', $dateFin);
		}
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}

	function findByIdCommunaute($idCommunaute) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('lot', '*')
		->where('id_fk_communaute_lot = ?', intval($idCommunaute));
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}

	function findByIdLot($idLot, $typeLot = null) {

		$liste = "";
		$nomChamp = "id_lot";

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
		$select->from('lot', '*');

		if ($typeLot != null) {
			$select->where('id_fk_type_lot = ?', $typeLot);
		}
		if ($liste != "") {
			$select->where($nomChamp .'='. $liste);
		} else {
			$select->where('id_lot = ?', intval($idLot));
		}
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}

	function findByIdBraldun($idBraldun) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('lot', '*')
		->where('id_fk_braldun_lot = ?', intval($idBraldun));
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}
}
