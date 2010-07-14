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
class ElementIngredient extends Zend_Db_Table {
	protected $_name = 'element_ingredient';
	protected $_primary = array('x_element_ingredient',  'y_element_ingredient', 'id_fk_type_element_ingredient');

	function selectVue($x_min, $y_min, $x_max, $y_max, $z) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('element_ingredient', '*')
		->from('type_ingredient', '*')
		->where('element_ingredient.id_fk_type_element_ingredient = type_ingredient.id_type_ingredient')
		->where('x_element_ingredient <= ?',$x_max)
		->where('x_element_ingredient >= ?',$x_min)
		->where('y_element_ingredient <= ?',$y_max)
		->where('y_element_ingredient >= ?',$y_min)
		->where('z_element_ingredient = ?',$z);
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findByCase($x, $y, $z) {
		return $this->selectVue($x, $y, $x, $y, $z);
	}

	function insertOrUpdate($data) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('element_ingredient', 'count(*) as nombre,
		quantite_element_ingredient as quantite')
		->where('id_fk_type_element_ingredient = ?',$data["id_fk_type_element_ingredient"])
		->where('x_element_ingredient = ?',$data["x_element_ingredient"])
		->where('y_element_ingredient = ?',$data["y_element_ingredient"])
		->group(array('quantite'));
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		$dateCreation = date("Y-m-d H:i:s");
		$nbJours = Bral_Util_De::get_2d10();
		$dateFin = Bral_Util_ConvertDate::get_date_add_day_to_date($dateCreation, $nbJours);
		$data["date_fin_element_ingredient"] = $dateFin;
		$dataUpdate["date_fin_element_ingredient"] = $dateFin;

		if (count($resultat) == 0) { // insert
			$this->insert($data);
		} else { // update
			$nombre = $resultat[0]["nombre"];
			$quantite = $resultat[0]["quantite"];
			$dataUpdate['quantite_element_ingredient']  = $quantite;

			if (isset($data["quantite_element_ingredient"])) {
				$dataUpdate['quantite_element_ingredient'] = $quantite + $data["quantite_element_ingredient"];
			}

			$where = ' id_fk_type_element_ingredient = '.$data["id_fk_type_element_ingredient"];
			$where .= ' AND x_element_ingredient = '.$data["x_element_ingredient"];
			$where .= ' AND y_element_ingredient = '.$data["y_element_ingredient"];

			if ($dataUpdate['quantite_element_ingredient'] <= 0) { // delete
				$this->delete($where);
			} else { // update
				$this->update($dataUpdate, $where);
			}
		}
	}

}
