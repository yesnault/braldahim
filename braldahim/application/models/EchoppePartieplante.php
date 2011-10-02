<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class EchoppePartieplante extends Zend_Db_Table
{
	protected $_name = 'echoppe_partieplante';
	protected $_primary = array('id_fk_type_echoppe_partieplante', 'id_echoppe_echoppe_partieplantefk_fk_');

	function findByIdEchoppe($idEchoppe)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('echoppe_partieplante', '*')
			->from('type_partieplante', '*')
			->from('type_plante', '*')
			->where('id_fk_echoppe_echoppe_partieplante = ?', intval($idEchoppe))
			->where('echoppe_partieplante.id_fk_type_echoppe_partieplante = type_partieplante.id_type_partieplante')
			->where('echoppe_partieplante.id_fk_type_plante_echoppe_partieplante = type_plante.id_type_plante');
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}

	function insertOrUpdate($data)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('echoppe_partieplante', 'count(*) as nombre,
		quantite_arriere_echoppe_partieplante as quantiteArriere, 
		quantite_preparee_echoppe_partieplante as quantitePreparee')
			->where('id_fk_type_echoppe_partieplante = ?', $data["id_fk_type_echoppe_partieplante"])
			->where('id_fk_echoppe_echoppe_partieplante = ?', $data["id_fk_echoppe_echoppe_partieplante"])
			->where('id_fk_type_plante_echoppe_partieplante = ?', $data["id_fk_type_plante_echoppe_partieplante"])
			->group(array('quantiteArriere', 'quantitePreparee'));
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		if (count($resultat) == 0) { // insert
			$this->insert($data);
		} else { // update
			$nombre = $resultat[0]["nombre"];
			$quantiteArriere = $resultat[0]["quantiteArriere"];
			$quantitePreparee = $resultat[0]["quantitePreparee"];

			if (isset($data["quantite_arriere_echoppe_partieplante"])) {
				$quantiteArriere = $quantiteArriere + $data["quantite_arriere_echoppe_partieplante"];
			}
			if (isset($data["quantite_preparee_echoppe_partieplante"])) {
				$quantitePreparee = $quantitePreparee + $data["quantite_preparee_echoppe_partieplante"];
			}

			if ($quantiteArriere < 0) $quantiteArriere = 0;
			if ($quantitePreparee < 0) $quantitePreparee = 0;

			$dataUpdate = array(
				'quantite_arriere_echoppe_partieplante' => $quantiteArriere,
				'quantite_preparee_echoppe_partieplante' => $quantitePreparee,
			);
			$where = ' id_fk_type_echoppe_partieplante = ' . $data["id_fk_type_echoppe_partieplante"];
			$where .= ' AND id_fk_echoppe_echoppe_partieplante = ' . $data["id_fk_echoppe_echoppe_partieplante"];
			$where .= ' AND id_fk_type_plante_echoppe_partieplante = ' . $data["id_fk_type_plante_echoppe_partieplante"];
			$this->update($dataUpdate, $where);
		}
	}
}
