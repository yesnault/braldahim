<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Butin extends Zend_Db_Table {
	protected $_name = 'butin';
	protected $_primary = array('id_butin');

	function findByIdBraldun($idBraldun) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('butin', '*')
		->where('id_fk_braldun_butin = ?', intval($idBraldun));
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}

	function findByCaseAndProprietaire($x, $y, $z) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('butin', '*')
		->where('z_butin = ?', intval($z))
		->where('x_butin = ?', intval($x))
		->where('y_butin = ?', intval($y));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findByCaseAndProprietaires($x, $y, $z, $listIdsBraldun) {
		if ($listIdsBraldun == null || count($listIdsBraldun) < 1) {
			return null;
		}

		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('butin', '*')
		->where('z_butin = ?', intval($z))
		->where('x_butin = ?', intval($x))
		->where('y_butin = ?', intval($y));

		$liste = "";
		foreach($listIdsBraldun as $id) {
			if ((int) $id."" == $id."") {
				if ($liste == "") {
					$liste = $id;
				} else {
					$liste = $liste." OR id_fk_braldun_butin =".$id;
				}
			}
		}

		$select->where('id_fk_braldun_butin = '.$liste);

		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findByCaseAndIdCommunaute($x, $y, $z, $idCommunaute) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('butin', '*')
		->from('braldun', '*')
		->where('z_butin = ?', intval($z))
		->where('x_butin = ?', intval($x))
		->where('y_butin = ?', intval($y))
		->where('id_fk_braldun_butin = id_braldun')
		->where('est_partage_communaute_butin_braldun = ?', 'oui')
		->where('id_fk_communaute_braldun = ?', $idCommunaute);

		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}
