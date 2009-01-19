<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: Hobbit.php 1000 2009-01-15 20:26:10Z yvonnickesnault $
 * $Author: yvonnickesnault $
 * $LastChangedDate: 2009-01-15 21:26:10 +0100 (Thu, 15 Jan 2009) $
 * $LastChangedRevision: 1000 $
 * $LastChangedBy: yvonnickesnault $
 */
class Hobbit extends Zend_Db_Table {
	protected $_name = 'hobbit';
	protected $_primary = 'id_hobbit';

	function findAllByDateCreationAndRegion($dateDebut, $dateFin) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('hobbit', 'count(id_hobbit) as nombre');
		$select->from('region', 'nom_region');
		$select->where('date_creation_hobbit >= ?', $dateDebut);
		$select->where('date_creation_hobbit <= ?', $dateFin);
		$select->where('est_compte_actif_hobbit = ?', 'oui');
		$select->where('id_region = id_fk_region_creation_hobbit');
		$select->order("nom_region ASC");
		$select->group("nom_region");
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
	function findAllByDateCreationAndFamille($dateDebut, $dateFin) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('hobbit', 'count(id_hobbit) as nombre');
		$select->from('nom', 'nom');
		$select->where('date_creation_hobbit >= ?', $dateDebut);
		$select->where('date_creation_hobbit <= ?', $dateFin);
		$select->where('est_compte_actif_hobbit = ?', 'oui');
		$select->where('id_nom = id_fk_nom_initial_hobbit');
		$select->order("nom ASC");
		$select->group("nom");
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
	function findAllByDateCreationAndSexe($dateDebut, $dateFin) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('hobbit', array('count(id_hobbit) as nombre', 'sexe_hobbit'));
		$select->where('date_creation_hobbit >= ?', $dateDebut);
		$select->where('date_creation_hobbit <= ?', $dateFin);
		$select->where('est_compte_actif_hobbit = ?', 'oui');
		$select->order("sexe_hobbit ASC");
		$select->group("sexe_hobbit");
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}