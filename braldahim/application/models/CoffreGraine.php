<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class CoffreGraine extends Zend_Db_Table
{
	protected $_name = 'coffre_graine';
	protected $_primary = array('id_fk_coffre_coffre_graine', 'id_fk_type_coffre_graine');

	function findByIdConteneur($idCoffre)
	{
		return $this->findByIdCoffre($idCoffre);
	}

	function findByIdCoffre($idCoffre)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('coffre_graine', '*')
			->from('type_graine', '*')
			->where('id_fk_coffre_coffre_graine = ?', intval($idCoffre))
			->where('coffre_graine.id_fk_type_coffre_graine = type_graine.id_type_graine');
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}

	function insertOrUpdate($data)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('coffre_graine', 'count(*) as nombre,
		quantite_coffre_graine as quantite')
			->where('id_fk_type_coffre_graine = ?', $data["id_fk_type_coffre_graine"])
			->where('id_fk_coffre_coffre_graine = ?', $data["id_fk_coffre_coffre_graine"])
			->group(array('quantite'));
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		if (count($resultat) == 0) { // insert
			$this->insert($data);
		} else { // update
			$nombre = $resultat[0]["nombre"];
			$quantite = $resultat[0]["quantite"];
			$dataUpdate['quantite_coffre_graine'] = $quantite;

			if (isset($data["quantite_coffre_graine"])) {
				$dataUpdate['quantite_coffre_graine'] = $quantite + $data["quantite_coffre_graine"];
			}

			$where = ' id_fk_type_coffre_graine = ' . $data["id_fk_type_coffre_graine"];
			$where .= ' AND id_fk_coffre_coffre_graine = ' . $data["id_fk_coffre_coffre_graine"];

			if ($dataUpdate['quantite_coffre_graine'] <= 0) { // delete
				$this->delete($where);
			} else { // update
				$this->update($dataUpdate, $where);
			}
		}
	}

}
