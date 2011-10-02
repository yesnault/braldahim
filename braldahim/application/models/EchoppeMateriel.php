<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class EchoppeMateriel extends Zend_Db_Table
{
	protected $_name = 'echoppe_materiel';
	protected $_primary = "id_echoppe_materiel";

	public function findByIdEchoppe($idEchoppe, $idMateriel = null)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('echoppe_materiel', '*')
			->from('type_materiel')
			->from('materiel')
			->where('id_echoppe_materiel = id_materiel')
			->where('id_fk_type_materiel = id_type_materiel')
			->where('id_fk_echoppe_echoppe_materiel = ?', $idEchoppe)
			->order(array('nom_type_materiel ASC'));
		if ($idMateriel != null) {
			$select->where('id_materiel = ?', intval($idMateriel));
		}
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}