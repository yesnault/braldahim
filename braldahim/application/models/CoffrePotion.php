<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class CoffrePotion extends Zend_Db_Table
{
	protected $_name = 'coffre_potion';
	protected $_primary = array('id_coffre_potion');

	function findByIdConteneur($idCoffre)
	{
		return $this->findByIdCoffre($idCoffre);
	}

	function findByIdCoffre($idCoffre)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('coffre_potion', '*')
			->from('type_potion')
			->from('type_qualite')
			->from('potion')
			->where('id_coffre_potion = id_potion')
			->where('id_fk_type_potion = id_type_potion')
			->where('id_fk_type_qualite_potion = id_type_qualite')
			->where('id_fk_coffre_coffre_potion = ?', intval($idCoffre));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}
