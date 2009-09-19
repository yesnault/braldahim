<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id$
 * $Author$
 * $LastChangedDate$
 * $LastChangedRevision$
 * $LastChangedBy$
 */
class Donjon extends Zend_Db_Table {
	protected $_name = 'donjon';
	protected $_primary = 'id_donjon';

	public function findByIdLieu($idLieu) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('donjon', '*')
		->from('region', '*')
		->where('id_fk_region_donjon = id_region')
		->where('id_fk_lieu_donjon = ?', intval($idLieu));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}