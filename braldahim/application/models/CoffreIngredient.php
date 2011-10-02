<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class CoffreIngredient extends Zend_Db_Table
{
	protected $_name = 'coffre_ingredient';
	protected $_primary = array('id_fk_coffre_coffre_ingredient', 'id_fk_type_coffre_ingredient');

	function findByIdConteneur($idCoffre)
	{
		return $this->findByIdCoffre($idCoffre);
	}

	function findByIdCoffre($idCoffre)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('coffre_ingredient', '*')
			->from('type_ingredient', '*')
			->where('id_fk_coffre_coffre_ingredient = ?', intval($idCoffre))
			->where('coffre_ingredient.id_fk_type_coffre_ingredient = type_ingredient.id_type_ingredient');
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}

	function insertOrUpdate($data)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('coffre_ingredient', 'count(*) as nombre,
		quantite_coffre_ingredient as quantite')
			->where('id_fk_type_coffre_ingredient = ?', $data["id_fk_type_coffre_ingredient"])
			->where('id_fk_coffre_coffre_ingredient = ?', $data["id_fk_coffre_coffre_ingredient"])
			->group(array('quantite'));
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		if (count($resultat) == 0) { // insert
			$this->insert($data);
		} else { // update
			$nombre = $resultat[0]["nombre"];
			$quantite = $resultat[0]["quantite"];
			$dataUpdate['quantite_coffre_ingredient'] = $quantite;

			if (isset($data["quantite_coffre_ingredient"])) {
				$dataUpdate['quantite_coffre_ingredient'] = $quantite + $data["quantite_coffre_ingredient"];
			}

			$where = ' id_fk_type_coffre_ingredient = ' . $data["id_fk_type_coffre_ingredient"];
			$where .= ' AND id_fk_coffre_coffre_ingredient = ' . $data["id_fk_coffre_coffre_ingredient"];

			if ($dataUpdate['quantite_coffre_ingredient'] <= 0) { // delete
				$this->delete($where);
			} else { // update
				$this->update($dataUpdate, $where);
			}
		}
	}

}
