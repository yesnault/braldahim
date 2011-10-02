<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class TypeEquipement extends Zend_Db_Table
{
	protected $_name = 'type_equipement';
	protected $_primary = "id_type_equipement";

	function findByIdMetier($idMetier, $ordre)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('type_equipement', '*')
			->where('id_fk_metier_type_equipement = ?', $idMetier)
			->order($ordre);

		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}

	function findAll($ordre)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('type_equipement', '*')
			->from('type_piece', '*')
			->where('id_type_piece = id_fk_type_piece_type_equipement')
			->order($ordre);

		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}
}
