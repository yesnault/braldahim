<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: Evenement.php 692 2008-12-08 21:05:57Z yvonnickesnault $
 * $Author: yvonnickesnault $
 * $LastChangedDate: 2008-12-08 22:05:57 +0100 (Mon, 08 Dec 2008) $
 * $LastChangedRevision: 692 $
 * $LastChangedBy: yvonnickesnault $
 */
class Evenement extends Zend_Db_Table {
	protected $_name = 'evenement';
	protected $_primary = 'id_evenement';

	function findTop10($dateDebut, $dateFin, $type) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('hobbit', array('nom_hobbit', 'prenom_hobbit', 'id_hobbit'));
		$select->from('evenement', 'count(id_evenement) as nombre');
		$select->where('id_fk_hobbit_evenement = id_hobbit');
		$select->where('id_fk_type_evenement = ?', $type);
		$select->where('date_evenement >= ?', $dateDebut);
		$select->where('date_evenement < ?', $dateFin);
		$select->order("nombre DESC");
		$select->group(array('nom_hobbit', 'prenom_hobbit', 'id_hobbit'));
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
		$select->order("nombre DESC");
		$select->group(array('nom_type_monstre'));
		$select->limit(10, 0);
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
	function findByFamille($dateDebut, $dateFin, $type) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('hobbit', null);
		$select->from('evenement', 'count(id_evenement) as nombre');
		$select->from('nom', 'nom');
		$select->where('id_fk_hobbit_evenement = id_hobbit');
		$select->where('id_nom = id_fk_nom_initial_hobbit');
		$select->where('id_fk_type_evenement = ?', $type);
		$select->where('date_evenement >= ?', $dateDebut);
		$select->where('date_evenement < ?', $dateFin);
		$select->order("nombre DESC");
		$select->group(array('nom'));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
	function findByNiveau($dateDebut, $dateFin, $type) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('evenement', array('count(id_evenement) as nombre', 'mod(niveau_evenement, 10) as niveau'));
		$select->where('id_fk_type_evenement = ?', $type);
		$select->where('date_evenement >= ?', $dateDebut);
		$select->where('date_evenement < ?', $dateFin);
		$select->order("nombre DESC");
		$select->group(array('niveau'));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
	function findBySexe($dateDebut, $dateFin, $type) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('hobbit', 'sexe_hobbit');
		$select->from('evenement', 'count(id_evenement) as nombre');
		$select->where('id_fk_hobbit_evenement = id_hobbit');
		$select->where('id_fk_type_evenement = ?', $type);
		$select->where('date_evenement >= ?', $dateDebut);
		$select->where('date_evenement < ?', $dateFin);
		$select->order("nombre DESC");
		$select->group(array('sexe_hobbit'));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}