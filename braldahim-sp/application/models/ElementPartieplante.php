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
class ElementPartieplante extends Zend_Db_Table
{
	protected $_name = 'element_partieplante';
	protected $_primary = array('id_fk_type_element_partieplante', 'id_fk_type_plante_element_partieplante', 'x_element_partieplante', 'y_element_partieplante');

	function selectVue($x_min, $y_min, $x_max, $y_max, $z)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('element_partieplante', '*')
			->from('type_partieplante', '*')
			->from('type_plante', '*')
			->where('element_partieplante.id_fk_type_element_partieplante = type_partieplante.id_type_partieplante')
			->where('element_partieplante.id_fk_type_plante_element_partieplante = type_plante.id_type_plante')
			->where('x_element_partieplante <= ?', $x_max)
			->where('x_element_partieplante >= ?', $x_min)
			->where('y_element_partieplante <= ?', $y_max)
			->where('y_element_partieplante >= ?', $y_min)
			->where('z_element_partieplante = ?', $z)
			->order(array('nom_type_plante', 'nom_type_partieplante'));
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
		$select->from('element_partieplante', 'count(*) as nombre,  quantite_element_partieplante as quantiteBrute,  quantite_preparee_element_partieplante as quantitePreparee')
			->where('id_fk_type_element_partieplante = ?', $data["id_fk_type_element_partieplante"])
			->where('x_element_partieplante = ?', $data["x_element_partieplante"])
			->where('y_element_partieplante = ?', $data["y_element_partieplante"])
			->where('id_fk_type_plante_element_partieplante = ?', $data["id_fk_type_plante_element_partieplante"])
			->group(array('quantiteBrute', 'quantitePreparee'));
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		$dateCreation = date("Y-m-d H:i:s");
		$nbJours = Bral_Util_De::get_2d10();
		$dateFin = Bral_Util_ConvertDate::get_date_add_day_to_date($dateCreation, $nbJours);
		$data["date_fin_element_partieplante"] = $dateFin;
		$dataUpdate["date_fin_element_partieplante"] = $dateFin;

		if (count($resultat) == 0) { // insert
			$this->insert($data);
		} else { // update
			$nombre = $resultat[0]["nombre"];
			$quantiteBrute = $resultat[0]["quantiteBrute"];
			$quantitePreparee = $resultat[0]["quantitePreparee"];

			$dataUpdate['quantite_element_partieplante'] = $quantiteBrute;
			$dataUpdate['quantite_preparee_element_partieplante'] = $quantitePreparee;

			if (isset($data["quantite_element_partieplante"])) {
				$dataUpdate['quantite_element_partieplante'] = $quantiteBrute + $data["quantite_element_partieplante"];
			}
			;

			if (isset($data["quantite_preparee_element_partieplante"])) {
				$dataUpdate['quantite_preparee_element_partieplante'] = $quantitePreparee + $data["quantite_preparee_element_partieplante"];
			}
			;

			$where = ' id_fk_type_element_partieplante = ' . $data["id_fk_type_element_partieplante"];
			$where .= ' AND x_element_partieplante = ' . $data["x_element_partieplante"];
			$where .= ' AND y_element_partieplante = ' . $data["y_element_partieplante"];
			$where .= ' AND id_fk_type_plante_element_partieplante = ' . $data["id_fk_type_plante_element_partieplante"];

			if ($dataUpdate['quantite_element_partieplante'] < 1 &&
				$dataUpdate['quantite_preparee_element_partieplante'] < 1
			) {
				$this->delete($where);
			} else { // update
				$this->update($dataUpdate, $where);
			}
		}
	}
}
