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
class Rune extends Zend_Db_Table {
	protected $_name = 'rune';
	protected $_primary = array('id_rune');

	function findByIdRuneWithDetails($idRune) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('rune', '*')
		->from('type_rune')
		->where('id_fk_type_rune = id_type_rune')
		->where('id_rune = ?', intval($idRune))
		->order(array("nom_type_rune"));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}
