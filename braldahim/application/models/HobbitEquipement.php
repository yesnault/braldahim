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
class HobbitEquipement extends Zend_Db_Table {
	protected $_name = 'hobbits_equipement';
	protected $_primary = array('id_equipement_hequipement');

	function findByIdHobbit($idHobbit) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('hobbits_equipement', '*')
		->from('recette_equipements')
		->from('type_equipement')
		->from('type_qualite')
		->from('type_emplacement')
		->from('type_piece')
		->from('equipement')
		->where('id_equipement = id_equipement_hequipement')
		->where('id_fk_recette_equipement = id_recette_equipement')
		->where('id_fk_type_recette_equipement = id_type_equipement')
		->where('id_fk_type_qualite_recette_equipement = id_type_qualite')
		->where('id_fk_type_emplacement_recette_equipement = id_type_emplacement')
		->where('id_fk_type_piece_type_equipement = id_type_piece')
		->where('id_fk_hobbit_hequipement = ?', intval($idHobbit))
		->joinLeft('mot_runique','id_fk_mot_runique_equipement = id_mot_runique');
		
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
    function findRunesOnly($idHobbit) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('equipement_rune', 'id_equipement_rune')
		->from('type_rune', '*')
		->from('hobbits_equipement', 'id_equipement_hequipement')
		->where('equipement_rune.id_fk_type_rune_equipement_rune = type_rune.id_type_rune')
		->where('hobbits_equipement.id_equipement_hequipement = equipement_rune.id_equipement_rune')
		->where('hobbits_equipement.id_fk_hobbit_hequipement = ?', intval($idHobbit));
		$sql = $select->__toString();
		
		return $db->fetchAll($sql);
    }
    
    function findByNomSystemeMot($idHobbit, $nomSystemeMot) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('hobbits_equipement', '*')
		->from('recette_equipements')
		->from('type_equipement')
		->from('type_qualite')
		->from('type_emplacement')
		->from('mot_runique')
		->from('equipement')
		->where('id_equipement = id_equipement_hequipement')
		->where('id_fk_recette_equipement = id_recette_equipement')
		->where('id_fk_type_recette_equipement = id_type_equipement')
		->where('id_fk_type_qualite_recette_equipement = id_type_qualite')
		->where('id_fk_type_emplacement_recette_equipement = id_type_emplacement')
		->where('id_fk_hobbit_hequipement = ?', intval($idHobbit))
		->where('id_fk_mot_runique_equipement = id_mot_runique')
		->where('nom_systeme_mot_runique = ?', $nomSystemeMot);
		
		$sql = $select->__toString();
		
		return $db->fetchAll($sql);
    }
    
	function findByTypePiece($idHobbit, $nomTypePiece) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('hobbits_equipement', '*')
		->from('type_equipement')
		->from('type_piece')
		->from('recette_equipements')
		->from('equipement')
		->where('id_equipement = id_equipement_hequipement')
		->where('id_fk_recette_equipement = id_recette_equipement')
		->where('id_fk_type_recette_equipement = id_type_equipement')
		->where('id_fk_type_piece_type_equipement = id_type_piece')
		->where('id_fk_hobbit_hequipement = ?', intval($idHobbit))
		->where('nom_systeme_type_piece = ?', $nomTypePiece);
		
		$sql = $select->__toString();
		
		return $db->fetchAll($sql);
    }
}
