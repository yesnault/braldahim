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
class EchoppePotion extends Zend_Db_Table {
	protected $_name = 'echoppe_potion';
	protected $_primary = "id_echoppe_potion";

	public function findByCriteres($ordre, $posStart, $count, $idRegion = -1, $bmTypePotion = -1, $idTypePotion = -1) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('echoppe_potion', '*');
		$select->from('type_potion');
		$select->from('type_qualite');
		$select->from('echoppe');
		$select->from('region');
		$select->from('potion');
		$select->from('metier', array('nom_masculin_metier', 'nom_feminin_metier'));
		$select->from('hobbit', array('id_hobbit', 'prenom_hobbit', 'nom_hobbit', 'sexe_hobbit'));
		$select->where('id_echoppe_potion = id_potion');
		$select->where('id_fk_type_potion = id_type_potion');
		$select->where('id_fk_type_qualite_potion = id_type_qualite');
		$select->where("type_vente_echoppe_potion like ?", "publique");
		$select->where('id_fk_hobbit_echoppe = id_hobbit');
		$select->where('id_fk_echoppe_echoppe_potion = id_echoppe');
		$select->where('id_fk_metier_echoppe = id_metier');
		$select->where('region.x_min_region <= echoppe.x_echoppe');
		$select->where('region.x_max_region >= echoppe.x_echoppe');
		$select->where('region.y_min_region <= echoppe.y_echoppe');
		$select->where('region.y_max_region >= echoppe.y_echoppe');
		if ($idTypePotion != -1) {
			$select->where('id_type_potion = ?', $idTypePotion);
		}
		if ($idRegion != -1) {
			$select->where('id_region = ?', $idRegion);
		}
		if ($bmTypePotion != -1) {
			$select->where('bm_type_potion like ?', $bmTypePotion);
		}
		
		if ($ordre != null) {
			$select->order($ordre);
		} else {
			$select->order("date_echoppe_potion DESC");
		}
		$select->limit($count, $posStart);
		
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
	public function countByCriteres($idRegion = -1, $bmTypePotion = -1, $idTypePotion = -1) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('echoppe_potion', 'count(id_echoppe_potion) as nombre');
		$select->from('type_potion', null);
		$select->from('type_qualite', null);
		$select->from('echoppe', null);
		$select->from('region', null);
		$select->from('metier', null);
		$select->from('hobbit', null);
		$select->from('potion', null);
		$select->where('id_echoppe_potion = id_potion');
		$select->where('id_fk_type_potion = id_type_potion');
		$select->where('id_fk_type_qualite_potion = id_type_qualite');
		$select->where("type_vente_echoppe_potion like ?", "publique");
		$select->where('id_fk_hobbit_echoppe = id_hobbit');
		$select->where('id_fk_echoppe_echoppe_potion = id_echoppe');
		$select->where('id_fk_metier_echoppe = id_metier');
		$select->where('region.x_min_region <= echoppe.x_echoppe');
		$select->where('region.x_max_region >= echoppe.x_echoppe');
		$select->where('region.y_min_region <= echoppe.y_echoppe');
		$select->where('region.y_max_region >= echoppe.y_echoppe');
		if ($idTypePotion != -1) {
			$select->where('id_type_potion = ?', $idTypePotion);
		}
		if ($idRegion != -1) {
			$select->where('id_region = ?', $idRegion);
		}
		if ($bmTypePotion != -1) {
			$select->where('bm_type_potion like ?', $bmTypePotion);
		}
		
		$sql = $select->__toString();
		$rowset = $db->fetchAll($sql);
		return $rowset[0]["nombre"];
	}
}
