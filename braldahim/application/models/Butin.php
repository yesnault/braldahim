<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Butin extends Zend_Db_Table {
	protected $_name = 'butin';
	protected $_primary = array('id_butin');

	function findByIdBraldun($idBraldun) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('butin', '*')
		->where('id_fk_braldun_butin = ?', intval($idBraldun));
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}
}
