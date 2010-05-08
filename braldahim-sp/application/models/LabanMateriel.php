<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: LabanMateriel.php 2019 2009-09-19 10:32:13Z yvonnickesnault $
 * $Author: yvonnickesnault $
 * $LastChangedDate: 2009-09-19 12:32:13 +0200 (sam., 19 sept. 2009) $
 * $LastChangedRevision: 2019 $
 * $LastChangedBy: yvonnickesnault $
 */
class LabanMateriel extends Zend_Db_Table {
	protected $_name = 'laban_materiel';
	protected $_primary = array('id_laban_materiel');

	function findByIdBraldun($idBraldun) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('laban_materiel', '*')
		->from('type_materiel')
		->from('materiel', '*')
		->where('id_laban_materiel = id_materiel')
		->where('id_fk_type_materiel = id_type_materiel')
		->where('id_fk_braldun_laban_materiel = ?', intval($idBraldun));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}