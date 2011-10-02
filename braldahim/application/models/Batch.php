<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Batch extends Zend_Db_Table
{
	protected $_name = 'batch';
	protected $_primary = array('id_batch');


	public function countByDate($dateDebut, $dateFin = null, $etat = null)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('batch', 'count(*) as nombre')
			->where('date_debut_batch >= ?', $dateDebut);
		if ($dateFin != null) {
			$select->where('date_debut_batch <= ?', $dateFin);
		}
		if ($etat != null) {
			$select->where("etat_batch like ?", $etat);
		}
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);
		return $resultat[0]["nombre"];
	}

	public function findByDate($dateDebut, $dateFin = null, $etat = null, $type = null)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('batch', '*')
			->where('date_debut_batch >= ?', $dateDebut);
		if ($dateFin != null) {
			$select->where('date_debut_batch <= ?', $dateFin);
		}
		$select->order("id_batch desc");
		if ($etat != null) {
			$select->where("etat_batch like ?", $etat);
		}
		if ($type != null) {
			$select->where("type_batch like ?", $type);
		}
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);
		return $resultat;
	}

	public function countAll()
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('batch', 'count(*) as nombre');
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);
		return $resultat[0]["nombre"];
	}

	public function countAllByEtat($etat)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('batch', 'count(*) as nombre')
			->where('etat_batch = ?', $etat);
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);
		return $resultat[0]["nombre"];
	}

}
