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

	public function findEnCoursByIdTerrain($idTerrain) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('soule_match', '*');
		$select->where('id_fk_terrain_soule_match = ?', (int)$idTerrain);
		$select->where('date_debut_soule_match is not null and date_fin_soule_match is null');
		$sql = $select->__toString();
		$result = $db->fetchAll($sql);
		return $result;
	}

	public function findNonDebuteByIdTerrain($idTerrain) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('soule_match', '*');
		$select->where('id_fk_terrain_soule_match = ?', (int)$idTerrain);
		$select->where('date_debut_soule_match is null and date_fin_soule_match is null');
		$sql = $select->__toString();
		$result = $db->fetchAll($sql);
		return $result;
	}

	public function findNonDebuteByIdHobbit($idHobbit) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('soule_equipe', null)
		->from('soule_match', '*')
		->from('soule_terrain', '*')
		->where('id_fk_hobbit_soule_equipe = ?', (int)$idHobbit)
		->where('id_fk_match_soule_equipe = id_soule_match')
		->where('date_debut_soule_match is null')
		->where('id_fk_terrain_soule_match = id_soule_terrain');
		$sql = $select->__toString();
		$result = $db->fetchAll($sql);
		return $result;
	}
	
	public function findNonDebutes() {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('soule_match', '*');
		$select->from('soule_terrain', '*');
		$select->where('id_fk_terrain_soule_match = id_soule_terrain');
		$select->where('date_debut_soule_match is null and date_fin_soule_match is null');
		$sql = $select->__toString();
		$result = $db->fetchAll($sql);
		return $result;
	}

	public function findByXYBallon($x, $y) {
		$db = $this->getAdapter();
		$select = $db->select();

		$select->from('soule_match', '*');
		$select->where('x_ballon_soule_match = ?', (int)$x);
		$select->where('y_ballon_soule_match = ?', (int)$y);
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	public function selectBallonVue($x_min, $y_min, $x_max, $y_max) {
		$db = $this->getAdapter();
		$select = $db->select();

		$select->from('soule_match', '*');
		$select->where('x_ballon_soule_match <= ?',$x_max);
		$select->where('x_ballon_soule_match >= ?',$x_min);
		$select->where('y_ballon_soule_match >= ?',$y_min);
		$select->where('y_ballon_soule_match <= ?',$y_max);

		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	public function findByIdHobbitBallon($idHobbit) {
		$db = $this->getAdapter();
		$select = $db->select();

		$select->from('soule_match', '*');
		$select->from('soule_terrain', '*');
		$select->where('id_fk_terrain_soule_match = id_soule_terrain');
		$select->where('id_fk_joueur_ballon_soule_match = ?', (int)$idHobbit);
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