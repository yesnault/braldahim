<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class BraldunsEquipement extends Zend_Db_Table
{
	protected $_name = 'bralduns_equipement';

	function findByIdBraldun($id_braldun)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('bralduns_equipement', '*')
			->from('type_equipement', '*')
			->where('bralduns_equipement.id_fk_braldun_hequipement = ?', intval($id_braldun))
			->where('bralduns_equipement.id_fk_type_hequipement = type_equipement.id_type_equipement');
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}