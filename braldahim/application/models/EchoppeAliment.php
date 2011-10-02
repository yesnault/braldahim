<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class EchoppeAliment extends Zend_Db_Table
{
	protected $_name = 'echoppe_aliment';
	protected $_primary = "id_echoppe_aliment";

	public function findByIdEchoppe($idEchoppe)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('echoppe_aliment', '*')
			->from('type_aliment')
			->from('aliment')
			->from('type_qualite')
			->where('id_echoppe_aliment = id_aliment')
			->where('id_fk_type_aliment = id_type_aliment')
			->where('id_fk_type_qualite_aliment = id_type_qualite')
			->where('id_fk_echoppe_echoppe_aliment = ?', $idEchoppe)
			->order(array('type_bbdf_type_aliment ASC', 'nom_type_aliment ASC', 'id_type_qualite ASC', 'bbdf_aliment ASC'));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}