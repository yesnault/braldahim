<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
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

	public function countNonDebuteByIdBraldun($idBraldun) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('soule_equipe', 'count(id_fk_braldun_soule_equipe) as nombre')
		->from('soule_match', null)
		->where('id_fk_braldun_soule_equipe = ?', (int)$idBraldun)
		->where('id_fk_match_soule_equipe = id_soule_match')
		->where('date_debut_soule_match is null');
		$sql = $select->__toString();
		$result = $db->fetchAll($sql);
		return $result[0]["nombre"];
	}

	public function countNonDebuteByIdBraldunList($listId) {
		if ($listId == null) {
			return null;
		}
		$nomChamp = "id_fk_braldun_soule_equipe";
		$liste = "";
		foreach($listId as $id) {
			if ((int) $id."" == $id."") {
				if ($liste == "") {
					$liste = $id;
				} else {
					$liste = $liste." OR ".$nomChamp."=".$id;
				}
			}
		}
		
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('soule_equipe', array('count(id_fk_braldun_soule_equipe) as nombre', 'id_fk_braldun_soule_equipe'))
		->from('soule_match', null)
		->where($nomChamp ."=".$liste)
		->where('id_fk_match_soule_equipe = id_soule_match')
		->where('date_debut_soule_match is null')
		->group('id_fk_braldun_soule_equipe');
		$sql = $select->__toString();
		return $db->fetchAll($sql);
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
		->from('braldun', '*')
		->where('id_soule_terrain = id_fk_terrain_soule_match')
		->where('id_fk_match_soule_equipe = id_soule_match')
		->where('date_debut_soule_match is null')
		->where('id_braldun = id_fk_braldun_soule_equipe')
		->where('id_fk_braldun_soule_equipe = id_braldun')
		->where('niveau_soule_terrain = ?', $niveauTerrain);

		$sql = $select->__toString();
		$result = $db->fetchAll($sql);
		return $result;
	}

	public function findByIdMatch($idMatch, $ordre = null) {
		$db = $this->getAdapter();
		$select = $db->select();

		$select->from('soule_equipe', '*')
		->from('braldun', '*')
		->where('id_fk_match_soule_equipe = ?', (int)$idMatch)
		->where('id_fk_braldun_soule_equipe = id_braldun');

		if ($ordre != null) {
			$select->order($ordre);
		}

		$sql = $select->__toString();
		$result = $db->fetchAll($sql);
		return $result;
	}
	
	public function findByIdMatchAndCamp($idMatch, $camp) {
		$db = $this->getAdapter();
		$select = $db->select();

		$select->from('soule_equipe', '*')
		->from('braldun', '*')
		->where('id_fk_match_soule_equipe = ?', (int)$idMatch)
		->where('camp_soule_equipe = ?', $camp)
		->where('id_fk_braldun_soule_equipe = id_braldun');

		$sql = $select->__toString();
		$result = $db->fetchAll($sql);
		return $result;
	}

	public function findByIdBraldunAndIdMatch($idBraldun, $idMatch) {
		$db = $this->getAdapter();
		$select = $db->select();

		$select->from('soule_equipe', '*')
		->where('id_fk_match_soule_equipe = ?', (int)$idMatch)
		->where('id_fk_braldun_soule_equipe = ?', (int)$idBraldun);

		$sql = $select->__toString();
		$result = $db->fetchAll($sql);
		if (count($result) != 1) {
			throw new Zend_Exception("SouleEquipe::findByIdBraldunAndIdMatch invalide:".$idMatch."-".$idBraldun);
		}
		return $result[0];
	}
}