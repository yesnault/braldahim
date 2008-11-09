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
class LabanPotion extends Zend_Db_Table {
	protected $_name = 'laban_potion';
	protected $_primary = array('id_laban_potion');

	function findByIdHobbit($idHobbit) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('laban_potion', '*')
		->from('type_potion')
		->from('type_qualite')
		->where('id_fk_type_laban_potion = id_type_potion')
		->where('id_fk_type_qualite_laban_potion = id_type_qualite')
		->where('id_fk_hobbit_laban_potion = ?', intval($idHobbit));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
    function countByIdHobbit($idHobbit) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('laban_potion', 'count(*) as nombre')
		->where('id_fk_hobbit_laban_potion = '.intval($idHobbit));
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		$nombre = $resultat[0]["nombre"];
		return $nombre;
    }
}
