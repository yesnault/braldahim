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
class EchoppeAliment extends Zend_Db_Table {
	protected $_name = 'echoppe_aliment';
	protected $_primary = "id_echoppe_aliment";

	public function findByCriteres($ordre, $posStart, $count, $idRegion = -1, $idTypeMateriel = -1) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('echoppe_aliment', '*');
		$select->from('type_aliment');
		$select->from('type_qualite');
		$select->from('echoppe');
		$select->from('region');
		$select->from('aliment');
		$select->from('metier', array('nom_masculin_metier', 'nom_feminin_metier'));
		$select->from('hobbit', array('id_hobbit', 'prenom_hobbit', 'nom_hobbit', 'sexe_hobbit'));
		$select->where('id_fk_type_qualite_aliment = id_type_qualite');
		$select->where("type_vente_echoppe_aliment like ?", "publique");
		$select->where('id_fk_hobbit_echoppe = id_hobbit');
		$select->where('id_fk_echoppe_echoppe_aliment = id_echoppe');
		$select->where('id_fk_metier_echoppe = id_metier');
		$select->where('id_echoppe_aliment = id_aliment');
		$select->where('id_fk_type_aliment = id_type_aliment');
		$select->where('region.x_min_region <= echoppe.x_echoppe');
		$select->where('region.x_max_region >= echoppe.x_echoppe');
		$select->where('region.y_min_region <= echoppe.y_echoppe');
		$select->where('region.y_max_region >= echoppe.y_echoppe');
		if ($idRegion != -1) {
			$select->where('id_region = ?', $idRegion);
		}
		if ($idTypeMateriel != -1) {
			$select->where('id_type_aliment = ?', $idTypeMateriel);
		}

		if ($ordre != null) {
			$select->order($ordre);
		} else {
			$select->order("date_echoppe_aliment DESC");
		}
		$select->limit($count, $posStart);

		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	public function countByCriteres($idRegion = -1, $idEmplacement = -1, $idTypeMateriel = -1) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('echoppe_aliment', 'count(id_echoppe_aliment) as nombre');
		$select->from('type_aliment', null);
		$select->from('echoppe', null);
		$select->from('hobbit', null);
		$select->from('region', null);
		$select->from('aliment', null);
		$select->where("type_vente_echoppe_aliment like ?", "publique");
		$select->where('id_fk_hobbit_echoppe = id_hobbit');
		$select->where('id_echoppe_aliment = id_aliment');
		$select->where('id_fk_type_aliment = id_type_aliment');
		$select->where('id_fk_echoppe_echoppe_aliment = id_echoppe');
		$select->where('region.x_min_region <= echoppe.x_echoppe');
		$select->where('region.x_max_region >= echoppe.x_echoppe');
		$select->where('region.y_min_region <= echoppe.y_echoppe');
		$select->where('region.y_max_region >= echoppe.y_echoppe');

		if ($idRegion != -1) {
			$select->where('id_region = ?', $idRegion);
		}
		if ($idTypeMateriel != -1) {
			$select->where('id_type_aliment = ?', $idTypeMateriel);
		}

		$sql = $select->__toString();
		$rowset = $db->fetchAll($sql);
		return $rowset[0]["nombre"];
	}
}