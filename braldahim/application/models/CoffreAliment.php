<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class CoffreAliment extends Zend_Db_Table
{
	protected $_name = 'coffre_aliment';
	protected $_primary = array('id_coffre_aliment');

	function findByIdConteneur($idCoffre)
	{
		return $this->findByIdCoffre($idCoffre);
	}

	function findByIdCoffre($idCoffre)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('coffre_aliment', '*')
			->from('type_aliment')
			->from('type_qualite')
			->from('aliment', '*')
			->where('id_aliment = id_coffre_aliment')
			->where('id_fk_type_aliment = id_type_aliment')
			->where('id_fk_type_qualite_aliment = id_type_qualite')
			->where('id_fk_coffre_coffre_aliment = ?', intval($idCoffre));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}
