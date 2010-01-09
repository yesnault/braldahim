<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: CharrettePotion.php 2229 2009-12-08 12:44:12Z yvonnickesnault $
 * $Author: yvonnickesnault $
 * $LastChangedDate: 2009-12-08 13:44:12 +0100 (mar., 08 déc. 2009) $
 * $LastChangedRevision: 2229 $
 * $LastChangedBy: yvonnickesnault $
 */
class CharrettePotion extends Zend_Db_Table {
	protected $_name = 'charrette_potion';
	protected $_primary = array('id_charrette_potion');

	function findByIdCharrette($idCharrette, $idTypePotion = null) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('charrette_potion', '*')
		->from('type_potion')
		->from('type_qualite')
		->from('potion')
		->where('id_charrette_potion = id_potion')
		->where('id_fk_type_potion = id_type_potion')
		->where('id_fk_type_qualite_potion = id_type_qualite')
		->where('id_fk_charrette_potion = ?', intval($idCharrette));
		if ($idTypePotion != null) {
			$select->where('id_type_potion = ?', intval($idTypePotion));
		}
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function countByIdCharrette($idCharrette) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('charrette_potion', 'count(*) as nombre')
		->where('id_fk_charrette_potion = '.intval($idCharrette));
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		$nombre = $resultat[0]["nombre"];
		return $nombre;
	}
}
