<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class DonjonCrevasse extends Zend_Db_Table
{
	protected $_name = 'donjon_crevasse';
	protected $_primary = "id_donjon_crevasse";

	function findByIdDonjon($idDonjon)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('donjon_crevasse', '*')
			->where('id_fk_donjon_crevasse = ?', intval($idDonjon));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}
