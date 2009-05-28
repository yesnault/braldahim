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
class Vente extends Zend_Db_Table {
	protected $_name = 'vente';
	protected $_primary = array('id_vente');

	function findByIdHobbit($idHobbit) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('vente', '*')
		->where('id_fk_hobbit_vente = '.intval($idHobbit));
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}
}