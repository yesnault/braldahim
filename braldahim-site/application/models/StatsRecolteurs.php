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
class StatsRecolteurs extends Zend_Db_Table
{
	protected $_name = 'stats_recolteurs';
	protected $_primary = array('id_stats_recolteurs');

	function findTop10($dateDebut, $dateFin, $type)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('braldun', array('nom_braldun', 'prenom_braldun', 'id_braldun'));
		$select->from('stats_recolteurs', $this->getSelectType($type));
		$select->where('id_fk_braldun_stats_recolteurs = id_braldun');
		$select->where('mois_stats_recolteurs >= ?', $dateDebut);
		$select->where('mois_stats_recolteurs < ?', $dateFin);
		$select->order("nombre DESC");
		$select->group(array('nom_braldun', 'prenom_braldun', 'id_braldun'));
		$select->limit(10, 0);
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findByFamille($dateDebut, $dateFin, $type)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('braldun', null);
		$select->from('stats_recolteurs', $this->getSelectType($type));
		$select->from('nom', 'nom');
		$select->where('id_fk_braldun_stats_recolteurs = id_braldun');
		$select->where('id_nom = id_fk_nom_initial_braldun');
		$select->where('mois_stats_recolteurs >= ?', $dateDebut);
		$select->where('mois_stats_recolteurs < ?', $dateFin);
		$select->order("nombre DESC");
		$select->group(array('nom'));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findByNiveau($dateDebut, $dateFin, $type)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('stats_recolteurs', array($this->getSelectType($type), 'floor(niveau_braldun_stats_recolteurs/10) as niveau'));
		$select->where('mois_stats_recolteurs >= ?', $dateDebut);
		$select->where('mois_stats_recolteurs < ?', $dateFin);
		$select->order("niveau ASC");
		$select->group(array('niveau'));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findBySexe($dateDebut, $dateFin, $type)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('braldun', 'sexe_braldun');
		$select->from('stats_recolteurs', $this->getSelectType($type));
		$select->where('id_fk_braldun_stats_recolteurs = id_braldun');
		$select->where('mois_stats_recolteurs >= ?', $dateDebut);
		$select->where('mois_stats_recolteurs < ?', $dateFin);
		$select->order("nombre DESC");
		$select->group(array('sexe_braldun'));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	private function getSelectType($type, $where = false)
	{
		$retour = "";
		switch ($type) {
			case "mineurs":
				$retour = "SUM(nb_minerai_stats_recolteurs)";
				break;
			case "herboristes":
				$retour = "SUM(nb_partieplante_stats_recolteurs)";
				break;
			case "chasseurs":
				$retour = "SUM(nb_peau_stats_recolteurs + nb_viande_stats_recolteurs)";
				break;
			case "bucherons":
				$retour = "SUM(nb_bois_stats_recolteurs)";
				break;
		}
		if (!$where) {
			$retour .= " as nombre";
		}
		return $retour;
	}
}