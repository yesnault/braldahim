<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class ElementGraine extends Zend_Db_Table
{
	protected $_name = 'element_graine';
	protected $_primary = array('x_element_graine', 'y_element_graine', 'id_fk_type_element_graine');

	function selectVue($x_min, $y_min, $x_max, $y_max, $z)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('element_graine', '*')
			->from('type_graine', '*')
			->where('element_graine.id_fk_type_element_graine = type_graine.id_type_graine')
			->where('x_element_graine <= ?', $x_max)
			->where('x_element_graine >= ?', $x_min)
			->where('y_element_graine <= ?', $y_max)
			->where('y_element_graine >= ?', $y_min)
			->where('z_element_graine = ?', $z);
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findByCase($x, $y, $z)
	{
		return $this->selectVue($x, $y, $x, $y, $z);
	}

	function insertOrUpdate($data)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('element_graine', 'count(*) as nombre,
		quantite_element_graine as quantite')
			->where('id_fk_type_element_graine = ?', $data["id_fk_type_element_graine"])
			->where('x_element_graine = ?', $data["x_element_graine"])
			->where('y_element_graine = ?', $data["y_element_graine"])
			->group(array('quantite'));
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		$dateCreation = date("Y-m-d H:i:s");
		$nbJours = Bral_Util_De::get_2d10();
		$dateFin = Bral_Util_ConvertDate::get_date_add_day_to_date($dateCreation, $nbJours);
		$data["date_fin_element_graine"] = $dateFin;
		$dataUpdate["date_fin_element_graine"] = $dateFin;

		if (count($resultat) == 0) { // insert
			$this->insert($data);
		} else { // update
			$nombre = $resultat[0]["nombre"];
			$quantite = $resultat[0]["quantite"];
			$dataUpdate['quantite_element_graine'] = $quantite;

			if (isset($data["quantite_element_graine"])) {
				$dataUpdate['quantite_element_graine'] = $quantite + $data["quantite_element_graine"];
			}

			$where = ' id_fk_type_element_graine = ' . $data["id_fk_type_element_graine"];
			$where .= ' AND x_element_graine = ' . $data["x_element_graine"];
			$where .= ' AND y_element_graine = ' . $data["y_element_graine"];

			if ($dataUpdate['quantite_element_graine'] <= 0) { // delete
				$this->delete($where);
			} else { // update
				$this->update($dataUpdate, $where);
			}
		}
	}

}
