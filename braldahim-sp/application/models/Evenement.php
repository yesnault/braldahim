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
class Evenement extends Zend_Db_Table
{
	protected $_name = 'evenement';
	protected $_primary = 'id_evenement';

	public function findByIdBraldun($idBraldun, $pageMin, $pageMax, $filtre)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('evenement', '*')
			->from('type_evenement', '*')
			->where('evenement.id_fk_type_evenement = type_evenement.id_type_evenement')
			->where('evenement.id_fk_braldun_evenement = ' . intval($idBraldun))
			->order('id_evenement DESC')
			->limitPage($pageMin, $pageMax);
		if ($filtre <> -1) {
			$select->where('type_evenement.id_type_evenement = ' . $filtre);
		}
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	public function findByIdMonstre($idMonstre, $pageMin, $pageMax, $filtre)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('evenement', '*')
			->from('type_evenement', '*')
			->where('evenement.id_fk_type_evenement = type_evenement.id_type_evenement')
			->where('evenement.id_fk_monstre_evenement = ' . intval($idMonstre))
			->order('id_evenement DESC')
			->limitPage($pageMin, $pageMax);
		if ($filtre <> -1) {
			$select->where('type_evenement.id_type_evenement = ' . $filtre);
		}
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	public function findByIdMatch($idMatch)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('braldun', array('nom_braldun', 'prenom_braldun', 'id_braldun'));
		$select->from('evenement', array('id_evenement', 'date_evenement', 'details_evenement'));
		$select->where('id_fk_braldun_evenement = id_braldun');
		$select->where('id_fk_soule_match_evenement = ?', (int)$idMatch);
		$select->order("date_evenement DESC");
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}