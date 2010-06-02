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
class CharrettePartage extends Zend_Db_Table {
	protected $_name = 'charrette_partage';
	protected $_primary = array('id_fk_charrette_partage', 'id_fk_braldun_charrette_partage');


	function findByIdCharrette($idCharrette) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('charrette_partage', '*')
		->from('braldun', '*')
		->where('id_fk_charrette_partage = ?', intval($idCharrette))
		->where('id_fk_braldun_charrette_partage = id_braldun');
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}
	
	function findByIdCharretteAndIdBraldun($idCharrette, $idBraldun) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('charrette_partage', '*')
		->where('id_fk_charrette_partage = ?', intval($idCharrette))
		->where('id_fk_braldun_charrette_partage = ?', intval($idBraldun));
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}
	
}
