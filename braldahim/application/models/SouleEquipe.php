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
class SouleEquipe extends Zend_Db_Table {
	protected $_name = 'soule_equipe';
	protected $_primary = 'id_soule_equipe';
	
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
		->where('id_fk_match_soule_equipe = id_soule_match')
		->where('date_debut_soule_match is null')
		->where('niveau_terrain_soule_match = ?', $niveauTerrain);
		
		$sql = $select->__toString();
		$result = $db->fetchAll($sql);
		return $result[0]["nombre"];
	}
}