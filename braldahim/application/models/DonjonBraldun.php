<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class DonjonBraldun extends Zend_Db_Table {
	protected $_name = 'donjon_braldun';
	protected $_primary = array('id_fk_braldun_donjon_braldun', 'id_fk_equipe_donjon_braldun');

	public function findByIdBraldunAndIdEquipe($idBraldun, $idDonjonEquipe) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('donjon_braldun', '*')
		->where('id_fk_braldun_donjon_braldun = ?', intval($idBraldun))
		->where("id_fk_equipe_donjon_braldun = ?", intval($idDonjonEquipe));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	public function findByIdEquipe($idDonjonEquipe) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('donjon_braldun', '*')
		->from('braldun', '*')
		->where('id_fk_braldun_donjon_braldun = id_braldun')
		->where("id_fk_equipe_donjon_braldun = ?", intval($idDonjonEquipe));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	public function findByIdBraldunNonTerminee($idBraldun) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('donjon_braldun', '*')
		->from('donjon_equipe', '*')
		->from('donjon', '*')
		->where("id_fk_equipe_donjon_braldun = id_donjon_equipe")
		->where('id_fk_donjon_equipe = id_donjon')
		->where('id_fk_braldun_donjon_braldun = ?', intval($idBraldun))
		->where("etat_donjon_equipe not like 'termine' AND etat_donjon_equipe not like 'annule'");
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

}