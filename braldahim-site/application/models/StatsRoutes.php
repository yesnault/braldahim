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
class StatsRoutes extends Zend_Db_Table {
	protected $_name = 'stats_routes';
	protected $_primary = array('id_stats_routes');
	
	function findTop10($dateDebut, $dateFin, $type, $config) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('hobbit', array('nom_hobbit', 'prenom_hobbit', 'id_hobbit'));
		$select->from('stats_routes', array('sum(nb_stats_routes) as nombre'));
		$select->where('id_fk_hobbit_stats_routes = id_hobbit');
		$select->where('mois_stats_routes >= ?', $dateDebut);
		$select->where('mois_stats_routes < ?', $dateFin);
		$select->where($this->getWhereType($type, $config));
		$select->order(array("nombre DESC"));
		$select->group(array('nom_hobbit', 'prenom_hobbit', 'id_hobbit'));
		$select->limit(10, 0);
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
	function findByFamille($dateDebut, $dateFin, $type, $config) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('hobbit', null);
		$select->from('stats_routes', array('sum(nb_stats_routes) as nombre'));
		$select->from('nom', 'nom');
		$select->where('id_fk_hobbit_stats_routes = id_hobbit');
		$select->where('id_nom = id_fk_nom_initial_hobbit');
		$select->where('mois_stats_routes >= ?', $dateDebut);
		$select->where('mois_stats_routes < ?', $dateFin);
		$select->where($this->getWhereType($type, $config));
		$select->order("nombre DESC");
		$select->group(array('nom'));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
	function findByNiveau($dateDebut, $dateFin, $type, $config) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('stats_routes', array('sum(nb_stats_routes) as nombre', 'floor(niveau_hobbit_stats_routes/10) as niveau'));
		$select->where('mois_stats_routes >= ?', $dateDebut);
		$select->where('mois_stats_routes < ?', $dateFin);
		$select->where($this->getWhereType($type, $config));
		$select->order("niveau ASC");
		$select->group(array('niveau'));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
	function findBySexe($dateDebut, $dateFin, $type, $config) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('hobbit', 'sexe_hobbit');
		$select->from('stats_routes', array('sum(nb_stats_routes) as nombre'));
		$select->where('id_fk_hobbit_stats_routes = id_hobbit');
		$select->where('mois_stats_routes >= ?', $dateDebut);
		$select->where('mois_stats_routes < ?', $dateFin);
		$select->where($this->getWhereType($type, $config));
		$select->order("nombre DESC");
		$select->group(array('sexe_hobbit'));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
	private function getWhereType($type, $config) {
		$retour = "";
		switch($type) {
			case "bucheronsroutes":
				$retour = "id_fk_metier_stats_routes = ".$config->game->metier->bucheron->id;
				break;
		}
		return $retour;
	}
}