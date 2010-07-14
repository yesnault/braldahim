<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: StatsExperience.php 2618 2010-05-08 14:25:37Z yvonnickesnault $
 * $Author: yvonnickesnault $
 * $LastChangedDate: 2010-05-08 16:25:37 +0200 (Sam, 08 mai 2010) $
 * $LastChangedRevision: 2618 $
 * $LastChangedBy: yvonnickesnault $
 */
class StatsReputation extends Zend_Db_Table {
	protected $_name = 'stats_reputation';
	protected $_primary = array('id_stats_reputation');

	function findTop10($dateDebut, $dateFin, $type = "gredin") {
		if ($type == "gredin") {
			$champ = "points_gredin_stats_reputation";
		} else {
			$champ = "points_redresseur_stats_reputation";
		}

		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('braldun', array('nom_braldun', 'prenom_braldun', 'id_braldun'));
		$select->from('stats_reputation', 'SUM('.$champ.') as nombre');
		$select->where('id_fk_braldun_stats_reputation = id_braldun');
		$select->where('mois_stats_reputation >= ?', $dateDebut);
		$select->where('mois_stats_reputation < ?', $dateFin);
		$select->order("nombre DESC");
		$select->group(array('nom_braldun', 'prenom_braldun', 'id_braldun'));
		$select->limit(10, 0);
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findByFamille($dateDebut, $dateFin, $type = "gredin") {
		if ($type == "gredin") {
			$champ = "points_gredin_stats_reputation";
		} else {
			$champ = "points_redresseur_stats_reputation";
		}

		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('braldun', null);
		$select->from('stats_reputation', 'SUM('.$champ.') as nombre');
		$select->from('nom', 'nom');
		$select->where('id_fk_braldun_stats_reputation = id_braldun');
		$select->where('id_nom = id_fk_nom_initial_braldun');
		$select->where('mois_stats_reputation >= ?', $dateDebut);
		$select->where('mois_stats_reputation < ?', $dateFin);
		$select->order("nombre DESC");
		$select->group(array('nom'));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findByNiveau($dateDebut, $dateFin, $type = "gredin") {
		if ($type == "gredin") {
			$champ = "points_gredin_stats_reputation";
		} else {
			$champ = "points_redresseur_stats_reputation";
		}

		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('stats_reputation', array('SUM('.$champ.') as nombre', 'floor(niveau_braldun_stats_reputation/10) as niveau'));
		$select->where('mois_stats_reputation >= ?', $dateDebut);
		$select->where('mois_stats_reputation < ?', $dateFin);
		$select->order("niveau ASC");
		$select->group(array('niveau'));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findBySexe($dateDebut, $dateFin, $type = "gredin") {
		if ($type == "gredin") {
			$champ = "points_gredin_stats_reputation";
		} else {
			$champ = "points_redresseur_stats_reputation";
		}

		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('braldun', 'sexe_braldun');
		$select->from('stats_reputation', 'SUM('.$champ.') as nombre');
		$select->where('id_fk_braldun_stats_reputation = id_braldun');
		$select->where('mois_stats_reputation >= ?', $dateDebut);
		$select->where('mois_stats_reputation < ?', $dateFin);
		$select->order("nombre DESC");
		$select->group(array('sexe_braldun'));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}