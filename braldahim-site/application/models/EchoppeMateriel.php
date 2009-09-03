<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: $
 * $Author: $
 * $LastChangedDate: $
 * $LastChangedRevision: $
 * $LastChangedBy: $
 */
class EchoppeMateriel extends Zend_Db_Table {
	protected $_name = 'echoppe_materiel';
	protected $_primary = "id_echoppe_materiel";

	public function findByCriteres($ordre, $posStart, $count, $idRegion = -1, $idTypeMateriel = -1) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('echoppe_materiel', '*');
		$select->from('type_materiel');
		$select->from('echoppe');
		$select->from('region');
		$select->from('materiel');
		$select->from('metier', array('nom_masculin_metier', 'nom_feminin_metier'));
		$select->from('hobbit', array('id_hobbit', 'prenom_hobbit', 'nom_hobbit', 'sexe_hobbit'));
		$select->where("type_vente_echoppe_materiel like ?", "publique");
		$select->where('id_fk_hobbit_echoppe = id_hobbit');
		$select->where('id_fk_echoppe_echoppe_materiel = id_echoppe');
		$select->where('id_fk_metier_echoppe = id_metier');
		$select->where('id_echoppe_materiel = id_materiel');
		$select->where('id_fk_type_materiel = id_type_materiel');
		$select->where('region.x_min_region <= echoppe.x_echoppe');
		$select->where('region.x_max_region >= echoppe.x_echoppe');
		$select->where('region.y_min_region <= echoppe.y_echoppe');
		$select->where('region.y_max_region >= echoppe.y_echoppe');
		if ($idRegion != -1) {
			$select->where('id_region = ?', $idRegion);
		}
		if ($idTypeMateriel != -1) {
			$select->where('id_type_materiel = ?', $idTypeMateriel);
		}

		if ($ordre != null) {
			$select->order($ordre);
		} else {
			$select->order("date_echoppe_materiel DESC");
		}
		$select->limit($count, $posStart);

		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	public function countByCriteres($idRegion = -1, $idEmplacement = -1, $idTypeMateriel = -1) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('echoppe_materiel', 'count(id_echoppe_materiel) as nombre');
		$select->from('type_materiel', null);
		$select->from('echoppe', null);
		$select->from('hobbit', null);
		$select->from('region', null);
		$select->from('materiel', null);
		$select->where("type_vente_echoppe_materiel like ?", "publique");
		$select->where('id_fk_hobbit_echoppe = id_hobbit');
		$select->where('id_echoppe_materiel = id_materiel');
		$select->where('id_fk_type_materiel = id_type_materiel');
		$select->where('id_fk_echoppe_echoppe_materiel = id_echoppe');
		$select->where('region.x_min_region <= echoppe.x_echoppe');
		$select->where('region.x_max_region >= echoppe.x_echoppe');
		$select->where('region.y_min_region <= echoppe.y_echoppe');
		$select->where('region.y_max_region >= echoppe.y_echoppe');

		if ($idRegion != -1) {
			$select->where('id_region = ?', $idRegion);
		}
		if ($idTypeMateriel != -1) {
			$select->where('id_type_materiel = ?', $idTypeMateriel);
		}

		$sql = $select->__toString();
		$rowset = $db->fetchAll($sql);
		return $rowset[0]["nombre"];
	}
}