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
class ElementTabac extends Zend_Db_Table {
	protected $_name = 'element_tabac';
	protected $_primary = array('id_fk_type_element_tabac');

	function selectVue($x_min, $y_min, $x_max, $y_max, $z) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('element_tabac', '*')
		->from('type_tabac', '*')
		->where('element_tabac.id_fk_type_element_tabac = type_tabac.id_type_tabac')
		->where('x_element_tabac <= ?',$x_max)
		->where('x_element_tabac >= ?',$x_min)
		->where('y_element_tabac <= ?',$y_max)
		->where('y_element_tabac >= ?',$y_min)
		->where('z_element_tabac = ?',$z);
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}

	function findByCase($x, $y, $z) {
		return $this->selectVue($x, $y, $x, $y, $z);
	}

	function insertOrUpdate($data) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('element_tabac', 'count(*) as nombre,
		quantite_feuille_element_tabac as quantiteFeuille')
		->where('id_fk_type_element_tabac = ?',$data["id_fk_type_element_tabac"])
		->where('x_element_tabac = ?',$data["x_element_tabac"])
		->where('y_element_tabac = ?',$data["y_element_tabac"])
		->group(array('quantiteFeuille'));
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		$dateCreation = date("Y-m-d H:i:s");
		$nbJours = Bral_Util_De::get_2d10();
		$dateFin = Bral_Util_ConvertDate::get_date_add_day_to_date($dateCreation, $nbJours);
		$data["date_fin_element_tabac"] = $dateFin;
		$dataUpdate["date_fin_element_tabac"] = $dateFin;

		if (count($resultat) == 0) { // insert
			$this->insert($data);
		} else { // update
			$nombre = $resultat[0]["nombre"];
			$quantiteFeuille = $resultat[0]["quantiteFeuille"];
				
			$dataUpdate['quantite_feuille_element_tabac']  = $quantiteFeuille;
				
			if (isset($data["quantite_feuille_element_tabac"])) {
				$dataUpdate['quantite_feuille_element_tabac'] = $quantiteFeuille + $data["quantite_feuille_element_tabac"];
			}
				
			$where = ' id_fk_type_element_tabac = '.$data["id_fk_type_element_tabac"];
			$where .= ' AND x_element_tabac = '.$data["x_element_tabac"];
			$where .= ' AND y_element_tabac = '.$data["y_element_tabac"];
				
			if ($dataUpdate['quantite_feuille_element_tabac'] <= 0) { // delete
				$this->delete($where);
			} else { // update
				$this->update($dataUpdate, $where);
			}
		}
	}

}
