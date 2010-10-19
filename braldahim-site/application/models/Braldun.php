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
class Braldun extends Zend_Db_Table {
	protected $_name = 'braldun';
	protected $_primary = 'id_braldun';

	function findNomById($id) {
		$where = $this->getAdapter()->quoteInto('id_braldun = ?',(int)$id);
		$braldun = $this->fetchRow($where);

		if ($braldun == null) {
			$retour = "braldun inconnu";
		} else {
			$retour = $braldun["prenom_braldun"]. " ".$braldun["nom_braldun"]. " (".$braldun["id_braldun"].")";
		}
		return $retour;
	}

	function findAllByDateCreationAndRegion($dateDebut, $dateFin) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('braldun', 'count(id_braldun) as nombre');
		$select->from('region', 'nom_region');
		$select->where('date_creation_braldun >= ?', $dateDebut);
		$select->where('date_creation_braldun <= ?', $dateFin);
		$select->where('est_compte_actif_braldun = ?', 'oui');
		$select->where('est_pnj_braldun = ?', "non");
		$select->where('id_region = id_fk_region_creation_braldun');
		$select->order("nom_region ASC");
		$select->group("nom_region");
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function countByNiveau($niveau) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('braldun', 'count(id_braldun) as nombre');
		$select->where('niveau_braldun = ?', $niveau);
		$select->where('est_pnj_braldun = ?', "non");
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		$nombre = $resultat[0]["nombre"];
		return $nombre;
	}

	public function findById($id){
		$where = $this->getAdapter()->quoteInto('id_braldun = ?',(int)$id);
		return $this->fetchRow($where);
	}

	function findAllByDateCreationAndFamille($dateDebut, $dateFin) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('braldun', 'count(id_braldun) as nombre');
		$select->from('nom', 'nom');
		$select->where('date_creation_braldun >= ?', $dateDebut);
		$select->where('date_creation_braldun <= ?', $dateFin);
		$select->where('est_compte_actif_braldun = ?', 'oui');
		$select->where('est_pnj_braldun = ?', "non");
		$select->where('id_nom = id_fk_nom_initial_braldun');
		$select->order("nom ASC");
		$select->group("nom");
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findAllByDateCreationAndSexe($dateDebut, $dateFin) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('braldun', array('count(id_braldun) as nombre', 'sexe_braldun'));
		$select->where('date_creation_braldun >= ?', $dateDebut);
		$select->where('date_creation_braldun <= ?', $dateFin);
		$select->where('est_compte_actif_braldun = ?', 'oui');
		$select->where('est_pnj_braldun = ?', "non");
		$select->order("sexe_braldun ASC");
		$select->group("sexe_braldun");
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findDistinctNiveaux() {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('braldun', 'distinct(niveau_braldun) as niveau');
		$select->where('est_compte_actif_braldun = ?', "oui");
		$select->where('est_pnj_braldun = ?', "non");
		$select->order("niveau ASC");
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function countByNiveauMinMax($niveauMin, $niveauMax) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('braldun', 'count(id_braldun) as nombre');
		$select->where('niveau_braldun >= ?', $niveauMin);
		$select->where('niveau_braldun <= ?', $niveauMax);
		$select->where('est_pnj_braldun = ?', "non");
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		$nombre = $resultat[0]["nombre"];
		return $nombre;
	}

	function findByNiveauxMinMaxAndCaracteristique($niveauMin, $niveauMax, $caracteristique) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('braldun', array('nom_braldun', 'prenom_braldun', 'id_braldun', $this->getSelectCaracteristique($caracteristique)));
		$select->where('niveau_braldun >= ?', $niveauMin);
		$select->where('niveau_braldun <= ?', $niveauMax);
		$select->where('est_compte_actif_braldun = ?', "oui");
		$select->where('est_pnj_braldun = ?', "non");
		$select->group(array('nom_braldun', 'prenom_braldun', 'id_braldun'));
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
				$retour = "max(force_base_braldun) as nombre";
				break;
			case "agilite" :
				$retour = "max(agilite_base_braldun) as nombre";
				break;
			case "vigueur" :
				$retour = "max(vigueur_base_braldun) as nombre";
				break;
			case "sagesse" :
				$retour = "max(sagesse_base_braldun) as nombre";
				break;
			case "armure_naturelle":
				$retour = "max(armure_naturelle_braldun) as nombre";
				break;
			case "regeneration":
				$retour = "max(regeneration_braldun) as nombre";
				break;
			case "poids_transportable":
				$retour = "max(poids_transportable_braldun) as nombre";
				break;
			case "duree_prochain_tour":
				$retour = "min(duree_prochain_tour_braldun) as nombre";
		}
		return $retour;
	}
}