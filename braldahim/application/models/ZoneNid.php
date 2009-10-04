<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: $
 * $Author: $
 * $LastChangedDate: $
 * $LastChangedRevision: $
 * $LastChangedBy: $
 */
class ZoneNid extends Zend_Db_Table {
	protected $_name = 'zone_nid';
	protected $_primary = 'id_zone_nid';

	function findZonesHorsVille() {
		return $this->findZonesNids('non');
	}

	function findZonesVille() {
		return $this->findZonesNids('oui');
	}

	function findById($idZoneNid) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('zone_nid', '*')
		->where('id_zone_nid = ?', intval($idZoneNid));
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}

	function findZonesNids($estVille) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('zone_nid', '*')
		->where('est_ville_zone_nid = ?', $estVille);
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}
}