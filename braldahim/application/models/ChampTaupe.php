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
class ChampTaupe extends Zend_Db_Table {
	protected $_name = 'champ_taupe';
	protected $_primary = "id_champ_taupe";

	function findByIdChamp($idChamp) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('champ_taupe', '*')
		->from('champ', '*')
		->where('id_champ = ?', $idChamp)
		->where('id_fk_champ_taupe = id_champ');
		
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
}
