<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: SouleMatch.php 2618 2010-05-08 14:25:37Z yvonnickesnault $
 * $Author: yvonnickesnault $
 * $LastChangedDate: 2010-05-08 16:25:37 +0200 (sam., 08 mai 2010) $
 * $LastChangedRevision: 2618 $
 * $LastChangedBy: yvonnickesnault $
 */
class SouleMessage extends Zend_Db_Table {
	protected $_name = 'soule_message';
	protected $_primary = 'id_soule_message';

	public function findByIdMatchAndCamp($idTerrain, $camp) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('soule_message', '*');
		$select->from('braldun', '*');
		$select->where('id_fk_match_soule_message = ?', (int)$idTerrain);
		$select->where('camp_soule_message like ?', $camp);
		$select->where('id_fk_braldun_soule_message = id_braldun');
		$select->order('id_soule_message desc');
		$sql = $select->__toString();
		$result = $db->fetchAll($sql);
		return $result;
	}
}