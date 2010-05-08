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
class Partage extends Zend_Db_Table {
	protected $_name = 'partage';
	protected $_primary = 'id_partage';

	function findByIdBraldun($idBraldun) {
		$where = "id_fk_braldun_declarant_partage = ".intval($idBraldun). " OR id_fk_braldun_declare_partage=".intval($idBraldun);
		
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('partage', '*')
		->where($where);
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}
