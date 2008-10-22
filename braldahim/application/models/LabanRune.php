<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id:$
 * $Author:$
 * $LastChangedDate:$
 * $LastChangedRevision:$
 * $LastChangedBy:$
 */
class LabanRune extends Zend_Db_Table {
	protected $_name = 'laban_rune';
	protected $_primary = array('id_laban_rune', 'id_fk_hobbit_laban_rune');
	
    function findByIdHobbit($idHobbit, $identifiee = null) {
    	$whereIdentifiee = "";
    	if ($identifiee != null) {
    		$whereIdentifiee = " AND laban_rune.est_identifiee_rune = '".$identifiee."'";
    	}
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('laban_rune', '*')
		->from('type_rune', '*')
		->where('id_fk_hobbit_laban_rune = '.intval($idHobbit))
		->where('laban_rune.id_fk_type_laban_rune = type_rune.id_type_rune'.$whereIdentifiee);
		$sql = $select->__toString();

		return $db->fetchAll($sql);
    }
    
    function countByIdHobbit($idHobbit) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('laban_rune', 'count(*) as nombre')
		->where('id_fk_hobbit_laban_rune = '.intval($idHobbit));
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		$nombre = $resultat[0]["nombre"];
		return $nombre;
    }
}
