<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Metier extends Zend_Db_Table
{
	protected $_name = 'metier';
	protected $_primary = 'id_metier';
	protected $_dependentTables = array('bralduns_metiers');

	function findAll()
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('metier', '*')
			->order('id_metier ASC');
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}