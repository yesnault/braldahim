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
class VenteRune extends Zend_Db_Table {
	protected $_name = 'vente_rune';
	protected $_primary = array('id_rune_vente_rune');
	
    function findByIdVente($idVente) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('vente_rune', '*')
		->from('type_rune', '*')
		->where('id_fk_vente_rune = '.intval($idVente))
		->where('vente_rune.id_fk_type_vente_rune = type_rune.id_type_rune');
		$sql = $select->__toString();
		return $db->fetchAll($sql);
    }
}
