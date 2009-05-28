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
class VentePotion extends Zend_Db_Table {
	protected $_name = 'vente_potion';
	protected $_primary = array('id_vente_potion');

	function findByIdVente($idVente) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('vente_potion', '*')
		->from('type_potion')
		->from('type_qualite')
		->where('id_fk_type_vente_potion = id_type_potion')
		->where('id_fk_type_qualite_vente_potion = id_type_qualite')
		->where('id_fk_vente_potion = ?', intval($idVente));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}
