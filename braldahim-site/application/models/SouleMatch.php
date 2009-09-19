<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id$
 * $Author$
 * $LastChangedDate$
 * $LastChangedRevision$
 * $LastChangedBy$
 */
class SouleMatch extends Zend_Db_Table {
	protected $_name = 'soule_match';
	protected $_primary = 'id_soule_match';

	public function fetchAllAvecTerrain() {
		$db = $this->getAdapter();
		$select = $db->select();

		$select->from('soule_match', '*');
		$select->from('soule_terrain', '*');
		$select->where('id_fk_terrain_soule_match = id_soule_terrain');
		$select->order('id_soule_match desc');
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
	public function findByIdMatch($idMatch) {
		$db = $this->getAdapter();
		$select = $db->select();

		$select->from('soule_match', '*');
		$select->from('soule_terrain', '*');
		$select->where('id_fk_terrain_soule_match = id_soule_terrain');
		$select->where('id_soule_match = ?', (int)$idMatch);
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}