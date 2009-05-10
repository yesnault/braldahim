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
class CharretteMateriel extends Zend_Db_Table {
	protected $_name = 'charrette_materiel';
	protected $_primary = array('id_charrette_materiel');

	function findByIdHobbit($idHobbit) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('charrette_materiel', '*')
		->from('type_materiel', '*')
		->where('id_fk_type_charrette_materiel = id_type_materiel')
		->where('id_fk_hobbit_charrette_materiel = ?', intval($idHobbit));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}