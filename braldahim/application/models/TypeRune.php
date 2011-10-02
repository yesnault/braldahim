<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class TypeRune extends Zend_Db_Table
{
	protected $_name = 'type_rune';
	protected $_primary = 'id_type_rune';

	function findByNiveau($niveau)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('type_rune', '*')
			->where('niveau_type_rune = ?', $niveau);
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}