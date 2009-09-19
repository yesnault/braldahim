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
class VenteEquipement extends Zend_Db_Table {
	protected $_name = 'vente_equipement';
	protected $_primary = array('id_vente_equipement');

	function findByIdVente($idVente) {
		
		$nomChamp = "id_fk_vente_equipement";
		$liste = "";
		if (!is_array($idVente)) {
			$liste = intval($idVente);
		} else {
			foreach($idVente as $id) {
				if ((int) $id."" == $id."") {
					if ($liste == "") {
						$liste = $id;
					} else {
						$liste = $liste." OR ".$nomChamp."=".$id;
					}
				}
			}
		}

		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('vente_equipement', '*')
		->from('recette_equipements')
		->from('type_equipement')
		->from('type_qualite')
		->from('type_emplacement')
		->from('type_piece')
		->from('vente')
		->from('hobbit', array('nom_hobbit', 'prenom_hobbit', 'id_hobbit'))
		->from('equipement')
		->from('type_ingredient')
		->where('id_type_ingredient = id_fk_type_ingredient_base_type_equipement')
		->where('id_equipement = id_vente_equipement')
		->where('id_fk_recette_equipement = id_recette_equipement')
		->where('id_fk_type_recette_equipement = id_type_equipement')
		->where('id_fk_type_qualite_recette_equipement = id_type_qualite')
		->where('id_fk_type_emplacement_recette_equipement = id_type_emplacement')
		->where('id_fk_type_piece_type_equipement = id_type_piece')
		->where('id_fk_vente_equipement ='. $liste)
		->where('id_fk_vente_equipement = id_vente')
		->where('id_fk_hobbit_vente = id_hobbit')
		->joinLeft('mot_runique','id_fk_mot_runique_equipement = id_mot_runique')
		->order('date_fin_vente desc');
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findAllByIdTypeEmplacement($idTypeEmplacement) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('vente_equipement', '*')
		->from('recette_equipements')
		->from('type_equipement')
		->from('type_qualite')
		->from('type_emplacement')
		->from('type_piece')
		->from('vente')
		->from('hobbit', array('nom_hobbit', 'prenom_hobbit', 'id_hobbit'))
		->from('equipement')
		->from('type_ingredient')
		->where('id_type_ingredient = id_fk_type_ingredient_base_type_equipement')
		->where('id_equipement = id_vente_equipement')
		->where('id_fk_recette_equipement = id_recette_equipement')
		->where('id_fk_type_recette_equipement = id_type_equipement')
		->where('id_fk_type_qualite_recette_equipement = id_type_qualite')
		->where('id_fk_type_emplacement_recette_equipement = id_type_emplacement')
		->where('id_type_emplacement = ?', $idTypeEmplacement)
		->where('id_fk_type_piece_type_equipement = id_type_piece')
		->where('id_fk_vente_equipement = id_vente')
		->where('id_fk_hobbit_vente = id_hobbit')
		->joinLeft('mot_runique','id_fk_mot_runique_equipement = id_mot_runique')
		->order('date_fin_vente desc');
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findAllByIdTypeEquipement($idTypeEquipement) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('vente_equipement', '*')
		->from('recette_equipements')
		->from('type_equipement')
		->from('type_qualite')
		->from('type_emplacement')
		->from('type_piece')
		->from('vente')
		->from('hobbit', array('nom_hobbit', 'prenom_hobbit', 'id_hobbit'))
		->from('equipement')
		->from('type_ingredient')
		->where('id_type_ingredient = id_fk_type_ingredient_base_type_equipement')
		->where('id_equipement = id_vente_equipement')
		->where('id_fk_recette_equipement = id_recette_equipement')
		->where('id_fk_type_recette_equipement = id_type_equipement')
		->where('id_fk_type_qualite_recette_equipement = id_type_qualite')
		->where('id_fk_type_emplacement_recette_equipement = id_type_emplacement')
		->where('id_type_equipement = ?', $idTypeEquipement)
		->where('id_fk_type_piece_type_equipement = id_type_piece')
		->where('id_fk_vente_equipement = id_vente')
		->where('id_fk_hobbit_vente = id_hobbit')
		->joinLeft('mot_runique','id_fk_mot_runique_equipement = id_mot_runique')
		->order('date_fin_vente desc');
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

}
