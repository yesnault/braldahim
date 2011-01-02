<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class CharretteRune extends Zend_Db_Table {
	protected $_name = 'charrette_rune';
	protected $_primary = array('id_rune_charrette_rune');

	function findByIdConteneur($idCharrette) {
		return $this->findByIdCharrette($idCharrette);
	}

	function countByIdConteneur($idCharrette) {
		return $this->countByIdCharrette($idCharrette);
	}
	
	function findByIdCharrette($idCharrette, $identifiee = null) {
		$whereIdentifiee = "";
		if ($identifiee != null) {
			$whereIdentifiee = " AND est_identifiee_rune = '".$identifiee."'";
		}
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('charrette_rune', '*')
		->from('type_rune', '*')
		->from('rune', '*')
		->where('id_rune_charrette_rune = id_rune')
		->where('id_fk_charrette_rune = '.intval($idCharrette))
		->where('id_fk_type_rune = id_type_rune'.$whereIdentifiee);
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}

	function countByIdCharrette($idCharrette) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('charrette_rune', 'count(*) as nombre')
		->where('id_fk_charrette_rune = '.intval($idCharrette));
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		$nombre = $resultat[0]["nombre"];
		return $nombre;
	}
}
