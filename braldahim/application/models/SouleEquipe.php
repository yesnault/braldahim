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
class SouleEquipe extends Zend_Db_Table {
	protected $_name = 'soule_equipe';
	protected $_primary = 'id_soule_equipe';

	public function countInscritsNonDebuteByNiveauTerrain($niveauTerrain) {
		$db = $this->getAdapter();
		$select = $db->select();

		$select->from('soule_equipe', array('camp_soule_equipe', 'count(camp_soule_equipe) as nombre'))
		->from('soule_match', null)
		->from('soule_terrain', null)
		->where('id_soule_terrain = id_fk_terrain_soule_match')
		->where('id_fk_match_soule_equipe = id_soule_match')
		->where('date_debut_soule_match is null')
		->where('niveau_soule_terrain = ?', $niveauTerrain)
		->group('camp_soule_equipe');

		$sql = $select->__toString();
		$result = $db->fetchAll($sql);
		return $result;
	}

	public function countInscritsNonDebuteByIdMatch($idMatch) {
		$db = $this->getAdapter();
		$select = $db->select();

		$select->from('soule_equipe', array('camp_soule_equipe', 'count(camp_soule_equipe) as nombre'))
		->from('soule_match', null)
		->where('id_fk_match_soule_equipe = ?', (int)$idMatch)
		->where('id_fk_match_soule_equipe = id_soule_match')
		->where('date_debut_soule_match is null')
		->group('camp_soule_equipe');

		$sql = $select->__toString();
		$result = $db->fetchAll($sql);
		return $result;
	}

	public function countNonDebuteByIdHobbit($idHobbit) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('soule_equipe', 'count(id_fk_hobbit_soule_equipe) as nombre')
		->from('soule_match', null)
		->where('id_fk_hobbit_soule_equipe = ?', (int)$idHobbit)
		->where('id_fk_match_soule_equipe = id_soule_match')
		->where('date_debut_soule_match is null');
		$sql = $select->__toString();
		$result = $db->fetchAll($sql);
		return $result[0]["nombre"];
	}

	public function countNonDebuteByNiveauTerrain($niveauTerrain) {
		$db = $this->getAdapter();
		$select = $db->select();

		$select->from('soule_equipe', 'count(*) as nombre')
		->from('soule_match', null)
		->from('soule_terrain', null)
		->where('id_soule_terrain = id_fk_terrain_soule_match')
		->where('id_fk_match_soule_equipe = id_soule_match')
		->where('date_debut_soule_match is null')
		->where('niveau_soule_terrain = ?', $niveauTerrain);

		$sql = $select->__toString();
		$result = $db->fetchAll($sql);
		return $result[0]["nombre"];
	}

	public function findNonDebuteByNiveauTerrain($niveauTerrain) {
		$db = $this->getAdapter();
		$select = $db->select();

		$select->from('soule_equipe', '*')
		->from('soule_match', null)
		->from('soule_terrain', null)
		->from('hobbit', '*')
		->where('id_soule_terrain = id_fk_terrain_soule_match')
		->where('id_fk_match_soule_equipe = id_soule_match')
		->where('date_debut_soule_match is null')
		->where('id_hobbit = id_fk_hobbit_soule_equipe')
		->where('id_fk_hobbit_soule_equipe = id_hobbit')
		->where('niveau_soule_terrain = ?', $niveauTerrain);

		$sql = $select->__toString();
		$result = $db->fetchAll($sql);
		return $result;
	}

	public function findByIdMatch($idMatch) {
		$db = $this->getAdapter();
		$select = $db->select();

		$select->from('soule_equipe', '*')
		->from('hobbit', '*')
		->where('id_fk_match_soule_equipe = ?', (int)$idMatch)
		->where('id_fk_hobbit_soule_equipe = id_hobbit');

		$sql = $select->__toString();
		$result = $db->fetchAll($sql);
		return $result;
	}
	
	public function findByIdHobbitAndIdMatch($idHobbit, $idMatch) {
		$db = $this->getAdapter();
		$select = $db->select();

		$select->from('soule_equipe', '*')
		->where('id_fk_match_soule_equipe = ?', (int)$idMatch)
		->where('id_fk_hobbit_soule_equipe = ?', (int)$idHobbit);

		$sql = $select->__toString();
		$result = $db->fetchAll($sql);
		if (count($result) != 1) {
			throw new Zend_Exception("SouleEquipe::findByIdHobbitAndIdMatch invalide:".$idMatch."-".$idHobbit);
		}
		return $result[0];
	}
	
}