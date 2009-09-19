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
class Potion extends Zend_Db_Table {
	protected $_name = 'potion';
	protected $_primary = array('id_potion');

	function findByIdPotionWithDetails($idPotion) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('potion', '*')
		->from('type_potion')
		->from('type_qualite')
		->where('id_fk_type_potion = id_type_potion')
		->where('id_fk_type_qualite_potion = id_type_qualite')
		->where('id_potion = ?', intval($idPotion))
		->order(array("type_potion", "nom_type_potion"));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}
