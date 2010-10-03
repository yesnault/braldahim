<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Evenement extends Zend_Db_Table {
	protected $_name = 'evenement';
	protected $_primary = 'id_evenement';

	function findTopPalmaresBraldun($dateDebut, $dateFin, $type) {
		$db = $this->getAdapter();

		$select = $db->select();
		$select->from('braldun', array('nom_braldun', 'prenom_braldun', 'id_braldun', 'niveau_braldun'));
		$select->from('evenement', 'count(id_evenement) as nombre');
		$select->where('id_fk_braldun_evenement = id_braldun');
		$select->where('id_fk_type_evenement = ?', $type);
		$select->where('date_evenement >= ?', $dateDebut);
		$select->where('date_evenement < ?', $dateFin);
		$select->where('id_fk_monstre_evenement IS NULL');
		$select->order("nombre DESC");
		$select->group(array('nom_braldun', 'prenom_braldun', 'id_braldun', 'niveau_braldun'));
		$select->limit(1, 0);
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);
		if ($resultat == null || count($resultat) < 1) {
			return null;
		}
		$nombre = $resultat[0]["nombre"];

		$select = $db->select();
		$select->from('braldun', array('nom_braldun', 'prenom_braldun', 'id_braldun', 'niveau_braldun'));
		$select->from('evenement', 'count(id_evenement) as nombre');
		$select->where('id_fk_braldun_evenement = id_braldun');
		$select->where('id_fk_type_evenement = ?', $type);
		$select->where('date_evenement >= ?', $dateDebut);
		$select->where('date_evenement < ?', $dateFin);
		$select->where('id_fk_monstre_evenement IS NULL');
		$select->order("nombre DESC");
		$select->group(array('nom_braldun', 'prenom_braldun', 'id_braldun', 'niveau_braldun'));
		$select->having('nombre = ?', $nombre);
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	public function findByIdBraldun($idBraldun, $pageMin, $pageMax, $filtre){
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('evenement', '*')
		->from('type_evenement', '*')
		->where('evenement.id_fk_type_evenement = type_evenement.id_type_evenement')
		->where('evenement.id_fk_braldun_evenement = '.intval($idBraldun))
		->order('id_evenement DESC')
		->limitPage($pageMin, $pageMax);
		if ($filtre <> -1) {
			$select->where('type_evenement.id_type_evenement = '.$filtre);
		}
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	public function findByIdMonstre($idMonstre, $pageMin, $pageMax, $filtre){
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('evenement', '*')
		->from('type_evenement', '*')
		->where('evenement.id_fk_type_evenement = type_evenement.id_type_evenement')
		->where('evenement.id_fk_monstre_evenement = '.intval($idMonstre))
		->order('id_evenement DESC')
		->limitPage($pageMin, $pageMax);
		if ($filtre <> -1) {
			$select->where('type_evenement.id_type_evenement = '.$filtre);
		}
		$sql = $select->__toString();
		return $db->fetchAll($sql);
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

	public function countByIdMonstreIdBraldunLast3tours($numTour, $idMonstre, $idBraldun, $actionEvenement) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('evenement', 'count(*) as nombre');
		$select->where('id_fk_braldun_evenement = ?', $idBraldun);
		$select->where('id_fk_monstre_evenement = ?', $idMonstre);
		$select->where('action_evenement like ?', $actionEvenement);
		$select->where('tour_monstre_evenement >= ?', $numTour-3);
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		$nombre = $resultat[0]["nombre"];
		return $nombre;
	}

	public function countByIdBraldunTourCourant($numTour, $idBraldun, $actionEvenement) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('evenement', 'count(*) as nombre');
		$select->where('id_fk_braldun_evenement = ?', $idBraldun);
		$select->where('action_evenement like ?', $actionEvenement);
		$select->where('tour_braldun_evenement = ?', $numTour);
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		$nombre = $resultat[0]["nombre"];
		return $nombre;
	}
}