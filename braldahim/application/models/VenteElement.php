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
class VenteElement extends Zend_Db_Table {
	protected $_name = 'vente_element';
	protected $_primary = array('id_vente_element');

	function findByIdVente($idVente) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('vente_element', '*')
		->where('id_fk_vente_element = '.intval($idVente));
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}
}