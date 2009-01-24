<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: Castar.php 595 2008-11-09 11:21:27Z yvonnickesnault $
 * $Author: yvonnickesnault $
 * $LastChangedDate: 2008-11-09 12:21:27 +0100 (Sun, 09 Nov 2008) $
 * $LastChangedRevision: 595 $
 * $LastChangedBy: yvonnickesnault $
 */
class StatsRecolteurs extends Zend_Db_Table {
	protected $_name = 'stats_recolteurs';
	protected $_primary = array('id_stats_recolteurs');
	
	function findTop10($dateDebut, $dateFin, $type) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('hobbit', array('nom_hobbit', 'prenom_hobbit', 'id_hobbit'));
		$select->from('stats_recolteurs', $this->getSelectType($type));
		$select->where('id_fk_hobbit_stats_recolteurs = id_hobbit');
		$select->where('mois_stats_recolteurs >= ?', $dateDebut);
		$select->where('mois_stats_recolteurs < ?', $dateFin);
		$select->order("nombre DESC");
		$select->group(array('nom_hobbit', 'prenom_hobbit', 'id_hobbit'));
		$select->limit(10, 0);
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
	function findByFamille($dateDebut, $dateFin, $type) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('hobbit', null);
		$select->from('stats_recolteurs', $this->getSelectType($type));
		$select->from('nom', 'nom');
		$select->where('id_fk_hobbit_stats_recolteurs = id_hobbit');
		$select->where('id_nom = id_fk_nom_initial_hobbit');
		$select->where('mois_stats_recolteurs >= ?', $dateDebut);
		$select->where('mois_stats_recolteurs < ?', $dateFin);
		$select->order("nombre DESC");
		$select->group(array('nom'));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
	function findByNiveau($dateDebut, $dateFin, $type) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('stats_recolteurs', array($this->getSelectType($type), 'floor(niveau_hobbit_stats_recolteurs/10) as niveau'));
		$select->where('mois_stats_recolteurs >= ?', $dateDebut);
		$select->where('mois_stats_recolteurs < ?', $dateFin);
		$select->order("niveau ASC");
		$select->group(array('niveau_hobbit_stats_recolteurs'));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
	function findBySexe($dateDebut, $dateFin, $type) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('hobbit', 'sexe_hobbit');
		$select->from('stats_recolteurs', $this->getSelectType($type));
		$select->where('id_fk_hobbit_stats_recolteurs = id_hobbit');
		$select->where('mois_stats_recolteurs >= ?', $dateDebut);
		$select->where('mois_stats_recolteurs < ?', $dateFin);
		$select->order("nombre DESC");
		$select->group(array('sexe_hobbit'));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
	private function getSelectType($type) {
		$retour = "";
		switch($type) {
			case "mineurs":
				$retour = "SUM(nb_minerai_stats_recolteurs) as nombre";
				break;
			case "herboristes":
				$retour = "SUM(nb_partieplante_stats_recolteurs) as nombre";
				break;
			case "chasseurs":
				$retour = "SUM(nb_peau_stats_recolteurs + nb_viande_stats_recolteurs) as nombre";
				break;
			case "bucherons":
				$retour = "SUM(nb_bois_stats_recolteurs) as nombre";
				break;
		}
		return $retour;
	}
}