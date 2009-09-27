<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: $
 * $Author: $
 * $LastChangedDate: $
 * $LastChangedRevision: $
 * $LastChangedBy: $
 */
class DonjonPalissade extends Zend_Db_Table {
	protected $_name = 'donjon_palissade';
	protected $_primary = "id_donjon_palissade";

	function findByIdDonjon($idDonjon) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('donjon_palissade', '*')
		->where('id_fk_donjon_palissade = ?',intval($idDonjon));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}
