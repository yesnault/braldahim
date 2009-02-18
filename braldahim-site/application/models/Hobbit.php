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
	
	function findDistinctNiveaux() {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('hobbit', 'distinct(niveau_hobbit) as niveau');
		$select->where('est_compte_actif_hobbit = ?', "oui");
		$select->order("niveau ASC");
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
	function findByNiveauAndCaracteristique($niveau, $caracteristique) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('hobbit', array('nom_hobbit', 'prenom_hobbit', 'id_hobbit', $this->getSelectCaracteristique($caracteristique)));
		$select->where('niveau_hobbit = ?', $niveau);
		$select->where('est_compte_actif_hobbit = ?', "oui");
		$select->group(array('nom_hobbit', 'prenom_hobbit', 'id_hobbit'));
		if ($caracteristique == "duree_prochain_tour") {
			$select->order("nombre ASC");
		} else {
			$select->order("nombre DESC");
		}
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
	private function getSelectCaracteristique($caracteristique) {
		$retour = "";
		switch($caracteristique) {
			case "force" :
				$retour = "max(force_base_hobbit) as nombre";
				break;
			case "agilite" :
				$retour = "max(agilite_base_hobbit) as nombre";
				break;
			case "vigueur" :
				$retour = "max(vigueur_base_hobbit) as nombre";
				break;
			case "sagesse" :
				$retour = "max(sagesse_base_hobbit) as nombre";
				break;
			case "armure_naturelle":
				$retour = "max(armure_naturelle_hobbit) as nombre";
				break;
			case "regeneration":
				$retour = "max(regeneration_hobbit) as nombre";
				break;
			case "poids_transportable":
				$retour = "max(poids_transportable_hobbit) as nombre";
				break;
			case "duree_prochain_tour":
				$retour = "min(duree_prochain_tour_hobbit) as nombre";
		}
		return $retour;
	}
}