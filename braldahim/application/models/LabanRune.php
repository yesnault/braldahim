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
class LabanRune extends Zend_Db_Table {
	protected $_name = 'laban_rune';
	protected $_primary = array('id_rune_laban_rune', 'id_fk_braldun_laban_rune');

	function findByIdBraldun($idBraldun, $identifiee = null, $ordre = null, $avecIdentifieur = false, $idRune = null) {
		$whereIdentifiee = "";
		if ($identifiee != null) {
			$whereIdentifiee = " AND est_identifiee_rune = '".$identifiee."'";
		}
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('laban_rune', '*')
		->from('type_rune', '*')
		->from('rune', '*')
		->where('id_rune_laban_rune = id_rune')
		->where('id_fk_braldun_laban_rune = ?', intval($idBraldun))
		->where('id_fk_type_rune = id_type_rune'.$whereIdentifiee);
		if ($ordre != null) {
			$select->order($ordre);
		}
		
		if ($avecIdentifieur) {
			$select->joinLeft('braldun','id_fk_braldun_identification_laban_rune = id_braldun');
		}
		
		if ($idRune != null) {
			$select->where('id_rune_laban_rune = ?', intval($idRune));
		}
		
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function countByIdBraldun($idBraldun) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('laban_rune', 'count(*) as nombre')
		->where('id_fk_braldun_laban_rune = ?', intval($idBraldun));
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		$nombre = $resultat[0]["nombre"];
		return $nombre;
	}
}
