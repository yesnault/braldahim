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
class EchoppeEquipement extends Zend_Db_Table {
	protected $_name = 'echoppe_equipement';
	protected $_primary = "id_echoppe_equipement";

	public function findByCriteres($ordre, $posStart, $count, $idRegion = -1, $idEmplacement = -1, $idTypeEquipement = -1) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('echoppe_equipement', '*');
		$select->from('recette_equipements');
		$select->from('type_equipement');
		$select->from('type_qualite');
		$select->from('type_emplacement');
		$select->from('echoppe');
		$select->from('region');
		$select->from('metier', array('nom_masculin_metier', 'nom_feminin_metier'));
		$select->from('hobbit', array('id_hobbit', 'prenom_hobbit', 'nom_hobbit', 'sexe_hobbit'));
		$select->where('id_fk_recette_echoppe_equipement = id_recette_equipement');
		$select->where('id_fk_type_recette_equipement = id_type_equipement');
		$select->where('id_fk_type_qualite_recette_equipement = id_type_qualite');
		$select->where('id_fk_type_emplacement_recette_equipement = id_type_emplacement');
		$select->where("type_vente_echoppe_equipement like ?", "publique");
		$select->where('id_fk_hobbit_echoppe = id_hobbit');
		$select->where('id_fk_echoppe_echoppe_equipement = id_echoppe');
		$select->where('id_fk_metier_echoppe = id_metier');
		$select->where('region.x_min_region <= echoppe.x_echoppe');
		$select->where('region.x_max_region >= echoppe.x_echoppe');
		$select->where('region.y_min_region <= echoppe.y_echoppe');
		$select->where('region.y_max_region >= echoppe.y_echoppe');
		if ($idEmplacement != -1) {
			$select->where('id_type_emplacement = ?', $idEmplacement);
		}
		if ($idRegion != -1) {
			$select->where('id_region = ?', $idRegion);
		}
		if ($idTypeEquipement != -1) {
			$select->where('id_type_equipement = ?', $idTypeEquipement);
		}
		$select->joinLeft('mot_runique','id_fk_mot_runique_echoppe_equipement = id_mot_runique');
		
		if ($ordre != null) {
			$select->order($ordre);
		} else {
			$select->order("date_echoppe_equipement DESC");
		}
		$select->limit($count, $posStart);
		
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
	public function countByCriteres($idRegion = -1, $idEmplacement = -1, $idTypeEquipement = -1) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('echoppe_equipement', 'count(id_echoppe_equipement) as nombre');
		$select->from('recette_equipements', null);
		$select->from('type_equipement', null);
		$select->from('type_qualite', null);
		$select->from('type_emplacement', null);
		$select->from('echoppe', null);
		$select->from('hobbit', null);
		$select->from('region', null);
		$select->where('id_fk_recette_echoppe_equipement = id_recette_equipement');
		$select->where('id_fk_type_recette_equipement = id_type_equipement');
		$select->where('id_fk_type_qualite_recette_equipement = id_type_qualite');
		$select->where('id_fk_type_emplacement_recette_equipement = id_type_emplacement');
		$select->where("type_vente_echoppe_equipement like ?", "publique");
		$select->where('id_fk_hobbit_echoppe = id_hobbit');
		$select->where('id_fk_echoppe_echoppe_equipement = id_echoppe');
		$select->where('region.x_min_region <= echoppe.x_echoppe');
		$select->where('region.x_max_region >= echoppe.x_echoppe');
		$select->where('region.y_min_region <= echoppe.y_echoppe');
		$select->where('region.y_max_region >= echoppe.y_echoppe');
		if ($idEmplacement != -1) {
			$select->where('id_type_emplacement = ?', $idEmplacement);
		}
		if ($idRegion != -1) {
			$select->where('id_region = ?', $idRegion);
		}
		if ($idTypeEquipement != -1) {
			$select->where('id_type_equipement = ?', $idTypeEquipement);
		}
		
		$sql = $select->__toString();
		$rowset = $db->fetchAll($sql);
		return $rowset[0]["nombre"];
	}
}
