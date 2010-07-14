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