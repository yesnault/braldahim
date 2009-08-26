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
class StatsFabricants extends Zend_Db_Table {
	protected $_name = 'stats_fabricants';
	protected $_primary = array('id_stats_fabricants');
	
	function findTop10($dateDebut, $dateFin, $type, $config) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('hobbit', array('nom_hobbit', 'prenom_hobbit', 'id_hobbit'));
		$select->from('stats_fabricants', array('sum(nb_piece_stats_fabricants) as nombre', 'sum(somme_niveau_piece_stats_fabricants)/sum(nb_piece_stats_fabricants) as moyenne'));
		$select->where('id_fk_hobbit_stats_fabricants = id_hobbit');
		$select->where('mois_stats_fabricants >= ?', $dateDebut);
		$select->where('mois_stats_fabricants < ?', $dateFin);
		$select->where($this->getWhereType($type, $config));
		$select->order(array("nombre DESC", "moyenne DESC"));
		$select->group(array('nom_hobbit', 'prenom_hobbit', 'id_hobbit'));
		$select->limit(10, 0);
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
	function findByFamille($dateDebut, $dateFin, $type, $config) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('hobbit', null);
		$select->from('stats_fabricants', array('sum(nb_piece_stats_fabricants) as nombre', 'sum(somme_niveau_piece_stats_fabricants)/sum(nb_piece_stats_fabricants) as moyenne'));
		$select->from('nom', 'nom');
		$select->where('id_fk_hobbit_stats_fabricants = id_hobbit');
		$select->where('id_nom = id_fk_nom_initial_hobbit');
		$select->where('mois_stats_fabricants >= ?', $dateDebut);
		$select->where('mois_stats_fabricants < ?', $dateFin);
		$select->where($this->getWhereType($type, $config));
		$select->order("nombre DESC");
		$select->group(array('nom'));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
	function findByNiveau($dateDebut, $dateFin, $type, $config) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('stats_fabricants', array('sum(nb_piece_stats_fabricants) as nombre', 'floor(niveau_hobbit_stats_fabricants/10) as niveau', 'sum(somme_niveau_piece_stats_fabricants)/sum(nb_piece_stats_fabricants) as moyenne'));
		$select->where('mois_stats_fabricants >= ?', $dateDebut);
		$select->where('mois_stats_fabricants < ?', $dateFin);
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
		$select->from('stats_fabricants', array('sum(nb_piece_stats_fabricants) as nombre', 'sum(somme_niveau_piece_stats_fabricants)/sum(nb_piece_stats_fabricants) as moyenne'));
		$select->where('id_fk_hobbit_stats_fabricants = id_hobbit');
		$select->where('mois_stats_fabricants >= ?', $dateDebut);
		$select->where('mois_stats_fabricants < ?', $dateFin);
		$select->where($this->getWhereType($type, $config));
		$select->order("nombre DESC");
		$select->group(array('sexe_hobbit'));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
	private function getWhereType($type, $config) {
		$retour = "";
		switch($type) {
			case "apothicaires":
				$retour = "id_fk_metier_stats_fabricants = ".$config->game->metier->apothicaire->id;
				break;
			case "menuisiers":
				$retour = "id_fk_metier_stats_fabricants = ".$config->game->metier->menuisier->id;
				break;
			case "forgerons":
				$retour = "id_fk_metier_stats_fabricants = ".$config->game->metier->forgeron->id;
				break;
			case "tanneurs":
				$retour = "id_fk_metier_stats_fabricants = ".$config->game->metier->tanneur->id;
				break;
			case "bucherons_palissades":
				$retour = "id_fk_metier_stats_fabricants = ".$config->game->metier->bucheron->id;
				break;
			case "cuisiniers":
				$retour = "id_fk_metier_stats_fabricants = ".$config->game->metier->cuisinier->id;
				break;
		}
		return $retour;
	}
}