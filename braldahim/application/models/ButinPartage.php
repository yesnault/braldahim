<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class ButinPartage extends Zend_Db_Table
{
	protected $_name = 'butin_partage';
	protected $_primary = array('id_fk_braldun_butin_partage', 'id_fk_autorise_butin_partage');

	function findByIdBraldun($idBraldun, $avecDetail = false)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('butin_partage', '*')
			->where('id_fk_braldun_butin_partage = ?', intval($idBraldun));

		if ($avecDetail) {
			$select->from('braldun', '*');
			$select->where('id_fk_autorise_butin_partage = id_braldun');
		}

		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findByIdBraldunAutorise($idBraldun)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('butin_partage', '*')
			->where('id_fk_autorise_butin_partage = ?', intval($idBraldun));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}
