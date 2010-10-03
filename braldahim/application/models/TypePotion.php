<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class TypePotion extends Zend_Db_Table {
	protected $_name = 'type_potion';
	protected $_primary = 'id_type_potion';

	function findAll() {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('type_potion', '*')
		->joinLeft('type_ingredient','id_fk_type_ingredient_type_potion = id_type_ingredient')
		->order("nom_type_potion");

		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}
