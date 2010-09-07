<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: BraldunEquipement.php 2786 2010-07-04 17:34:56Z yvonnickesnault $
 * $Author: yvonnickesnault $
 * $LastChangedDate: 2010-07-04 19:34:56 +0200 (dim., 04 juil. 2010) $
 * $LastChangedRevision: 2786 $
 * $LastChangedBy: yvonnickesnault $
 */
class BraldunEquipement extends Zend_Db_Table {
	protected $_name = 'bralduns_equipement';
	protected $_primary = array('id_equipement_hequipement');

	function findByIdBraldun($idBraldun) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('bralduns_equipement', '*')
		->where('id_fk_braldun_hequipement = ?', intval($idBraldun));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}
