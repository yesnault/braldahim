<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class EchoppeIngredient extends Zend_Db_Table
{
	protected $_name = 'echoppe_ingredient';
	protected $_primary = array('id_fk_echoppe_echoppe_ingredient', 'id_fk_type_echoppe_ingredient');

	function findByIdEchoppe($idEchoppe)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('echoppe_ingredient', '*')
			->from('type_ingredient', '*')
			->where('id_fk_echoppe_echoppe_ingredient = ?', intval($idEchoppe))
			->where('echoppe_ingredient.id_fk_type_echoppe_ingredient = type_ingredient.id_type_ingredient');
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}

	function insertOrUpdate($data)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from(
			'echoppe_ingredient', 'count(*) as nombre,
		quantite_arriere_echoppe_ingredient as quantiteArriere')
			->where('id_fk_type_echoppe_ingredient = ?', $data["id_fk_type_echoppe_ingredient"])
			->where('id_fk_echoppe_echoppe_ingredient = ?', $data["id_fk_echoppe_echoppe_ingredient"])
			->group(array('quantiteArriere'));
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		if (count($resultat) == 0) { // insert
			$this->insert($data);
		} else { // update
			$nombre = $resultat[0]["nombre"];
			$quantiteArriere = $resultat[0]["quantiteArriere"];

			if (isset($data["quantite_arriere_echoppe_ingredient"])) {
				$quantiteArriere = $quantiteArriere + $data["quantite_arriere_echoppe_ingredient"];
			}

			if ($quantiteArriere < 0) $quantiteArriere = 0;

			$dataUpdate = array(
				'quantite_arriere_echoppe_ingredient' => $quantiteArriere,
			);
			$where = ' id_fk_type_echoppe_ingredient = ' . $data["id_fk_type_echoppe_ingredient"];
			$where .= ' AND id_fk_echoppe_echoppe_ingredient = ' . $data["id_fk_echoppe_echoppe_ingredient"];

			if ($quantiteArriere == 0) { // delete
				$this->delete($where);
			} else { // update
				$this->update($dataUpdate, $where);
			}


		}
	}

}
