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
class StatsMotsRuniques extends Zend_Db_Table {
	protected $_name = 'stats_mots_runiques';
	protected $_primary = array('id_stats_mots_runiques');
	
	function findByMot($dateDebut, $dateFin) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('mot_runique', 'suffixe_mot_runique as suffixe');
		$select->from('type_piece', null);
		$select->from('stats_mots_runiques', array('sum(nb_piece_stats_mots_runiques) as nombre'));
		$select->where('id_fk_mot_runique_stats_mots_runiques = id_mot_runique');
		$select->where('id_fk_type_piece_stats_mots_runiques = id_type_piece');
		$select->where('mois_stats_mots_runiques >= ?', $dateDebut);
		$select->where('mois_stats_mots_runiques < ?', $dateFin);
		$select->order("nombre DESC");
		$select->group(array('suffixe'));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
	function findByNiveauPiece($dateDebut, $dateFin) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('mot_runique', 'suffixe_mot_runique as suffixe');
		$select->from('type_piece', null);
		$select->from('stats_mots_runiques', array('sum(nb_piece_stats_mots_runiques) as nombre', 'niveau_piece_stats_mots_runiques as niveau'));
		$select->where('id_fk_mot_runique_stats_mots_runiques = id_mot_runique');
		$select->where('id_fk_type_piece_stats_mots_runiques = id_type_piece');
		$select->where('mois_stats_mots_runiques >= ?', $dateDebut);
		$select->where('mois_stats_mots_runiques < ?', $dateFin);
		$select->order("niveau ASC");
		$select->group(array('niveau', 'suffixe'));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
	function findByTypePiece($dateDebut, $dateFin) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('mot_runique', 'suffixe_mot_runique as suffixe');
		$select->from('type_piece', 'nom_type_piece as nomPiece');
		$select->from('stats_mots_runiques', array('sum(nb_piece_stats_mots_runiques) as nombre'));
		$select->where('id_fk_mot_runique_stats_mots_runiques = id_mot_runique');
		$select->where('id_fk_type_piece_stats_mots_runiques = id_type_piece');
		$select->where('mois_stats_mots_runiques >= ?', $dateDebut);
		$select->where('mois_stats_mots_runiques < ?', $dateFin);
		$select->order("nombre DESC");
		$select->group(array('nomPiece', 'suffixe'));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}