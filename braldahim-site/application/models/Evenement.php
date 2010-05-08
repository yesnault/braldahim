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
class Evenement extends Zend_Db_Table {
	protected $_name = 'evenement';
	protected $_primary = 'id_evenement';

	function findTop10($dateDebut, $dateFin, $type, $braldunOnly = false) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('braldun', array('nom_braldun', 'prenom_braldun', 'id_braldun'));
		$select->from('evenement', 'count(id_evenement) as nombre');
		$select->where('id_fk_braldun_evenement = id_braldun');
		$select->where('id_fk_type_evenement = ?', $type);
		$select->where('date_evenement >= ?', $dateDebut);
		$select->where('date_evenement < ?', $dateFin);
		if ($braldunOnly) { 
			$select->where('id_fk_monstre_evenement IS NULL');
		}
		$select->order("nombre DESC");
		$select->group(array('nom_braldun', 'prenom_braldun', 'id_braldun'));
		$select->limit(10, 0);
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
	function findTop10Monstres($dateDebut, $dateFin, $type) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('monstre', array('niveau_monstre', 'id_monstre'));
		$select->from('type_monstre', 'nom_type_monstre');
		$select->from('evenement', 'count(id_evenement) as nombre');
		$select->where('id_fk_monstre_evenement = id_monstre');
		$select->where('id_fk_type_evenement = ?', $type);
		$select->where('id_fk_type_monstre = id_type_monstre');
		$select->where('date_evenement >= ?', $dateDebut);
		$select->where('date_evenement < ?', $dateFin);
		$select->where('est_mort_monstre = ?', 'non');
		$select->order("nombre DESC");
		$select->group(array('niveau_monstre', 'id_monstre', 'nom_type_monstre'));
		$select->limit(10, 0);
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
	function findByTypeMonstres($dateDebut, $dateFin, $type) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('monstre', null);
		$select->from('type_monstre', 'nom_type_monstre');
		$select->from('evenement', 'count(id_evenement) as nombre');
		$select->where('id_fk_monstre_evenement = id_monstre');
		$select->where('id_fk_type_evenement = ?', $type);
		$select->where('id_fk_type_monstre = id_type_monstre');
		$select->where('date_evenement >= ?', $dateDebut);
		$select->where('date_evenement < ?', $dateFin);
		$select->where('est_mort_monstre = ?', 'non');
		$select->order("nombre DESC");
		$select->group(array('nom_type_monstre'));
		$select->limit(10, 0);
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
	function findByFamille($dateDebut, $dateFin, $type, $braldunOnly = false) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('braldun', null);
		$select->from('evenement', 'count(id_evenement) as nombre');
		$select->from('nom', 'nom');
		$select->where('id_fk_braldun_evenement = id_braldun');
		$select->where('id_nom = id_fk_nom_initial_braldun');
		$select->where('id_fk_type_evenement = ?', $type);
		$select->where('date_evenement >= ?', $dateDebut);
		$select->where('date_evenement < ?', $dateFin);
		if ($braldunOnly) {
			$select->where('id_fk_monstre_evenement IS NULL');
		}
		$select->order("nombre DESC");
		$select->group(array('nom'));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
	function findByNiveau($dateDebut, $dateFin, $type, $braldunOnly = false) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('evenement', array('count(id_evenement) as nombre', 'floor(niveau_evenement/10) as niveau'));
		$select->where('id_fk_type_evenement = ?', $type);
		$select->where('date_evenement >= ?', $dateDebut);
		$select->where('date_evenement < ?', $dateFin);
		$select->order("niveau ASC");
		if ($braldunOnly) {
			$select->where('id_fk_monstre_evenement IS NULL');
		}
		$select->group(array('niveau'));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
	function findBySexe($dateDebut, $dateFin, $type, $braldunOnly = false) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('braldun', 'sexe_braldun');
		$select->from('evenement', 'count(id_evenement) as nombre');
		$select->where('id_fk_braldun_evenement = id_braldun');
		$select->where('id_fk_type_evenement = ?', $type);
		$select->where('date_evenement >= ?', $dateDebut);
		$select->where('date_evenement < ?', $dateFin);
		if ($braldunOnly) {
			$select->where('id_fk_monstre_evenement IS NULL');
		}
		$select->order("nombre DESC");
		$select->group(array('sexe_braldun'));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
	function findByType($dateDebut, $dateFin, $type, $ordre, $posStart, $count) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('braldun', array('nom_braldun', 'prenom_braldun', 'id_braldun'));
		$select->from('evenement', array('id_evenement', 'date_evenement', 'details_evenement'));
		$select->where('id_fk_braldun_evenement = id_braldun');
		$select->where('id_fk_type_evenement = ?', $type);
		$select->where('date_evenement >= ?', $dateDebut);
		$select->where('date_evenement < ?', $dateFin);
		
		if ($ordre != null) {
			$select->order($ordre);
		} else {
			$select->order("date_evenement DESC");
		}
		$select->limit($count, $posStart);
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
	function countByType($dateDebut, $dateFin, $type) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('evenement', 'count(id_evenement) as nombre');
		$select->where('id_fk_type_evenement = ?', $type);
		$select->where('date_evenement >= ?', $dateDebut);
		$select->where('date_evenement < ?', $dateFin);
		$select->order("nombre DESC");
		$sql = $select->__toString();
		$rowset = $db->fetchAll($sql);
		return $rowset[0]["nombre"];
	}
	
	public function findByIdMatch($idMatch) {
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