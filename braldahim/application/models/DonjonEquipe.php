<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class DonjonEquipe extends Zend_Db_Table
{
	protected $_name = 'donjon_equipe';
	protected $_primary = 'id_donjon_equipe';

	public function findNonTermineeByIdDonjon($idDonjon)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('donjon_equipe', '*')
			->from('donjon', '*')
			->where('id_fk_donjon_equipe = id_donjon')
			->where('id_fk_donjon_equipe = ?', intval($idDonjon))
			->where("etat_donjon_equipe not like 'termine' AND etat_donjon_equipe not like 'annule'");
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	public function findNonTerminee()
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('donjon_equipe', '*')
			->where("etat_donjon_equipe not like 'termine' AND etat_donjon_equipe not like 'annule'");
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}