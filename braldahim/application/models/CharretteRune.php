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
class CharretteRune extends Zend_Db_Table {
	protected $_name = 'charrette_rune';
	protected $_primary = array('id_charrette_rune', 'id_fk_hobbit_charrette_rune');
	
    function findByIdHobbit($idHobbit, $identifiee = null) {
    	$whereIdentifiee = "";
    	if ($identifiee != null) {
    		$whereIdentifiee = " AND charrette_rune.est_identifiee_charrette_rune = '".$identifiee."'";
    	}
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('charrette_rune', '*')
		->from('type_rune', '*')
		->where('id_fk_hobbit_charrette_rune = '.intval($idHobbit))
		->where('charrette_rune.id_fk_type_charrette_rune = type_rune.id_type_rune'.$whereIdentifiee);
		$sql = $select->__toString();

		return $db->fetchAll($sql);
    }
    
    function countByIdHobbit($idHobbit) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('charrette_rune', 'count(*) as nombre')
		->where('id_fk_hobbit_charrette_rune = '.intval($idHobbit));
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		$nombre = $resultat[0]["nombre"];
		return $nombre;
    }
}
