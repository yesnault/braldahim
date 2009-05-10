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
class CharretteAmelioration extends Zend_Db_Table {
	protected $_name = 'charrette_amelioration';
	protected $_primary = array('id_charrette_amelioration', 'id_materiel_charrette_amelioration');

	function findByIdCharrette($idCharrette) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('charrette_amelioration', '*')
		->from('type_materiel', '*')
		->where('id_fk_type_charrette_amelioration = id_type_materiel')
		->where('id_charrette_amelioration = ?', intval($idCharrette));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}
