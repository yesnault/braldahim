<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class CoffrePartieplante extends Zend_Db_Table
{
	protected $_name = 'coffre_partieplante';
	protected $_primary = array('id_fk_type_coffre_partieplante', 'id_fk_coffre_coffre_partieplante');

	function findByIdConteneur($idCoffre)
	{
		return $this->findByIdCoffre($idCoffre);
	}

	function findByIdCoffre($idCoffre)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('coffre_partieplante', '*')
			->from('type_partieplante', '*')
			->from('type_plante', '*')
			->where('id_fk_coffre_coffre_partieplante = ?', intval($idCoffre))
			->where('coffre_partieplante.id_fk_type_coffre_partieplante = type_partieplante.id_type_partieplante')
			->where('coffre_partieplante.id_fk_type_plante_coffre_partieplante = type_plante.id_type_plante')
			->order(array('nom_type_plante', 'nom_type_partieplante'));
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}

	function insertOrUpdate($data)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('coffre_partieplante', 'count(*) as nombre, quantite_coffre_partieplante as quantiteBrute,  quantite_preparee_coffre_partieplante as quantitePreparee')
			->where('id_fk_type_coffre_partieplante = ?', $data["id_fk_type_coffre_partieplante"])
			->where('id_fk_coffre_coffre_partieplante = ?', $data["id_fk_coffre_coffre_partieplante"])
			->where('id_fk_type_plante_coffre_partieplante = ?', $data["id_fk_type_plante_coffre_partieplante"])
			->group(array('quantiteBrute', 'quantitePreparee'));
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		if (count($resultat) == 0) { // insert
			$this->insert($data);
		} else { // update
			$nombre = $resultat[0]["nombre"];
			$quantiteBrute = $resultat[0]["quantiteBrute"];
			$quantitePreparee = $resultat[0]["quantitePreparee"];

			$dataUpdate['quantite_coffre_partieplante'] = $quantiteBrute;
			$dataUpdate['quantite_preparee_coffre_partieplante'] = $quantitePreparee;

			if (isset($data["quantite_coffre_partieplante"])) {
				$quantiteBrute += $data["quantite_coffre_partieplante"];
			}
			;

			if (isset($data["quantite_preparee_coffre_partieplante"])) {
				$quantitePreparee += $data["quantite_preparee_coffre_partieplante"];
			}
			;

			$dataUpdate = array(
				'quantite_coffre_partieplante' => $quantiteBrute,
				'quantite_preparee_coffre_partieplante' => $quantitePreparee,
			);

			$where = ' id_fk_type_coffre_partieplante = ' . $data["id_fk_type_coffre_partieplante"];
			$where .= ' AND id_fk_coffre_coffre_partieplante = ' . $data["id_fk_coffre_coffre_partieplante"];
			$where .= ' AND id_fk_type_plante_coffre_partieplante = ' . $data["id_fk_type_plante_coffre_partieplante"];
			$this->update($dataUpdate, $where);
		}
	}
}
