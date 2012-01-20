<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class BraldunEquipement extends Zend_Db_Table
{
	protected $_name = 'bralduns_equipement';
	protected $_primary = array('id_equipement_hequipement');

	function findByIdBraldun($idBraldun, $idEquipement = null)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('bralduns_equipement', '*')
			->from('recette_equipements')
			->from('type_equipement')
			->from('type_qualite')
			->from('type_emplacement')
			->from('type_piece')
			->from('equipement')
			->from('type_ingredient')
			->where('id_type_ingredient = id_fk_type_ingredient_base_type_equipement')
			->where('id_equipement = id_equipement_hequipement')
			->where('id_fk_recette_equipement = id_recette_equipement')
			->where('id_fk_type_recette_equipement = id_type_equipement')
			->where('id_fk_type_qualite_recette_equipement = id_type_qualite')
			->where('id_fk_type_emplacement_recette_equipement = id_type_emplacement')
			->where('id_fk_type_piece_type_equipement = id_type_piece')
			->where('id_fk_braldun_hequipement = ?', intval($idBraldun))
			->joinLeft('mot_runique', 'id_fk_mot_runique_equipement = id_mot_runique');

		if ($idEquipement != null) {
			$select->where('id_equipement = ?', intval($idEquipement));
		}

		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findRunesOnly($idBraldun)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('equipement_rune', 'id_equipement_rune')
			->from('type_rune', '*')
			->from('rune', '*')
			->from('bralduns_equipement', 'id_equipement_hequipement')
			->where('id_rune_equipement_rune = id_rune')
			->where('id_fk_type_rune = id_type_rune')
			->where('bralduns_equipement.id_equipement_hequipement = equipement_rune.id_equipement_rune')
			->where('bralduns_equipement.id_fk_braldun_hequipement = ?', intval($idBraldun));
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}

	function findByNomSystemeMot($idBraldun, $nomSystemeMot)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('bralduns_equipement', '*')
			->from('recette_equipements')
			->from('type_equipement')
			->from('type_qualite')
			->from('type_emplacement')
			->from('mot_runique')
			->from('equipement')
			->from('type_ingredient')
			->where('id_type_ingredient = id_fk_type_ingredient_base_type_equipement')
			->where('id_equipement = id_equipement_hequipement')
			->where('id_fk_recette_equipement = id_recette_equipement')
			->where('id_fk_type_recette_equipement = id_type_equipement')
			->where('id_fk_type_qualite_recette_equipement = id_type_qualite')
			->where('id_fk_type_emplacement_recette_equipement = id_type_emplacement')
			->where('id_fk_braldun_hequipement = ?', intval($idBraldun))
			->where('id_fk_mot_runique_equipement = id_mot_runique')
			->where('nom_systeme_mot_runique = ?', $nomSystemeMot);

		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findByTypePiece($idBraldun, $nomTypePiece)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('bralduns_equipement', '*')
			->from('type_equipement')
			->from('type_piece')
			->from('recette_equipements')
			->from('equipement')
            ->from('equipement_bonus')
			->where('id_equipement = id_equipement_hequipement')
			->where('id_fk_recette_equipement = id_recette_equipement')
			->where('id_fk_type_recette_equipement = id_type_equipement')
			->where('id_fk_type_piece_type_equipement = id_type_piece')
            ->where('id_equipement = id_equipement_bonus')
			->where('id_fk_braldun_hequipement = ?', intval($idBraldun))
			->where('nom_systeme_type_piece = ?', $nomTypePiece);

		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}
}
