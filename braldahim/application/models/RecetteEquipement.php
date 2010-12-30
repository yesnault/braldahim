<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class RecetteEquipement extends Zend_Db_Table {
	protected $_name = 'recette_equipements';
	protected $_primary = "id_recette_equipement";

	function findByIdTypeAndNiveauAndQualite($idType, $niveau, $qualite) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('recette_equipements', '*')
		->from('type_equipement')
		->from('type_qualite')
		->from('type_emplacement')
		->from('type_piece')
		->where('id_fk_type_recette_equipement = ?',$idType)
		->where('niveau_recette_equipement = ?',$niveau)
		->where('id_fk_type_qualite_recette_equipement = ?',$qualite)
		->where('id_fk_type_recette_equipement = id_type_equipement')
		->where('id_fk_type_qualite_recette_equipement = id_type_qualite')
		->where('id_fk_type_emplacement_recette_equipement = id_type_emplacement')
		->where('id_fk_type_piece_type_equipement = id_type_piece');

		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findByIdTypeEquipement($idType) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('recette_equipements', '*')
		->from('type_equipement')
		->from('type_qualite')
		->from('type_emplacement')
		->from('type_piece')
		->where('id_fk_type_recette_equipement = ?',$idType)
		->where('id_fk_type_recette_equipement = id_type_equipement')
		->where('id_fk_type_qualite_recette_equipement = id_type_qualite')
		->where('id_fk_type_emplacement_recette_equipement = id_type_emplacement')
		->where('id_fk_type_piece_type_equipement = id_type_piece')
		->order(array('niveau_recette_equipement', 'id_fk_type_qualite_recette_equipement'));

		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findByIdDonjon($idDonjon) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('recette_equipements', '*')
		->from('type_equipement')
		->from('type_qualite')
		->from('type_emplacement')
		->where('id_fk_donjon_type_equipement = ?', intval($idDonjon))
		->where('id_fk_type_recette_equipement = id_type_equipement')
		->where('id_fk_type_qualite_recette_equipement = id_type_qualite')
		->where('id_fk_type_emplacement_recette_equipement = id_type_emplacement')
		->order(array('niveau_recette_equipement', 'id_fk_type_qualite_recette_equipement'));

		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}
