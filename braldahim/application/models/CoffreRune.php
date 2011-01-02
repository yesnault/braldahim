<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class CoffreRune extends Zend_Db_Table {
	protected $_name = 'coffre_rune';
	protected $_primary = array('id_rune_coffre_rune');

	function findByIdConteneur($idCoffre) {
		return $this->findByIdCoffre($idCoffre);
	}

	function findByIdCoffre($idCoffre, $identifiee = null) {
		$whereIdentifiee = "";
		if ($identifiee != null) {
			$whereIdentifiee = " AND est_identifiee_rune = '".$identifiee."'";
		}
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('coffre_rune', '*')
		->from('type_rune', '*')
		->from('rune', '*')
		->where('id_rune_coffre_rune = id_rune')
		->where('id_fk_coffre_coffre_rune = ? ', intval($idCoffre))
		->where('id_fk_type_rune = id_type_rune'.$whereIdentifiee);
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}
}
