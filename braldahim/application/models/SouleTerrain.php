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
class SouleTerrain extends Zend_Db_Table {
	protected $_name = 'soule_terrain';
	protected $_primary = 'id_soule_terrain';

	public function findByIdTerrain($idTerrain) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('soule_terrain', '*');
		$select->where('id_soule_terrain = ?', (int)$idTerrain);
		$sql = $select->__toString();
		$result = $db->fetchAll($sql);
		return $result[0];
	}

	public function findByNiveau($niveauTerrain) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('soule_terrain', '*');
		$select->where('niveau_soule_terrain = ?', (int)$niveauTerrain);
		$sql = $select->__toString();
		$result = $db->fetchAll($sql);
		return $result[0];
	}

	function findByCase($x, $y) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('soule_terrain', '*')
		->where('x_min_soule_terrain <= ?',$x)
		->where('x_max_soule_terrain >= ?',$x)
		->where('y_min_soule_terrain <= ?',$y)
		->where('y_max_soule_terrain >= ?',$y);
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}

}