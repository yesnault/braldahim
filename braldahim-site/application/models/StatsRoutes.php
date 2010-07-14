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
class StatsRoutes extends Zend_Db_Table {
	protected $_name = 'stats_routes';
	protected $_primary = array('id_stats_routes');
	
	function findTop10($dateDebut, $dateFin, $type) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('braldun', array('nom_braldun', 'prenom_braldun', 'id_braldun'));
		$select->from('stats_routes', array('sum(nb_stats_routes) as nombre'));
		$select->where('id_fk_braldun_stats_routes = id_braldun');
		$select->where('mois_stats_routes >= ?', $dateDebut);
		$select->where('mois_stats_routes < ?', $dateFin);
		$select->where($this->getWhereType($type));
		$select->order(array("nombre DESC"));
		$select->group(array('nom_braldun', 'prenom_braldun', 'id_braldun'));
		$select->limit(10, 0);
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
	function findByFamille($dateDebut, $dateFin, $type) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('braldun', null);
		$select->from('stats_routes', array('sum(nb_stats_routes) as nombre'));
		$select->from('nom', 'nom');
		$select->where('id_fk_braldun_stats_routes = id_braldun');
		$select->where('id_nom = id_fk_nom_initial_braldun');
		$select->where('mois_stats_routes >= ?', $dateDebut);
		$select->where('mois_stats_routes < ?', $dateFin);
		$select->where($this->getWhereType($type));
		$select->order("nombre DESC");
		$select->group(array('nom'));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
	function findByNiveau($dateDebut, $dateFin, $type) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('stats_routes', array('sum(nb_stats_routes) as nombre', 'floor(niveau_braldun_stats_routes/10) as niveau'));
		$select->where('mois_stats_routes >= ?', $dateDebut);
		$select->where('mois_stats_routes < ?', $dateFin);
		$select->where($this->getWhereType($type));
		$select->order("niveau ASC");
		$select->group(array('niveau'));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
	function findBySexe($dateDebut, $dateFin, $type) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('braldun', 'sexe_braldun');
		$select->from('stats_routes', array('sum(nb_stats_routes) as nombre'));
		$select->where('id_fk_braldun_stats_routes = id_braldun');
		$select->where('mois_stats_routes >= ?', $dateDebut);
		$select->where('mois_stats_routes < ?', $dateFin);
		$select->where($this->getWhereType($type));
		$select->order("nombre DESC");
		$select->group(array('sexe_braldun'));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
	private function getWhereType($type) {
		Zend_Loader::loadClass("Bral_Util_Metier");
		
		$retour = "";
		switch($type) {
			case "bucheronsroutes":
				$retour = "id_fk_metier_stats_routes = ".Bral_Util_Metier::METIER_BUCHERON_ID;
				break;
		}
		return $retour;
	}
}