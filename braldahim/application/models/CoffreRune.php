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
class CoffreRune extends Zend_Db_Table {
	protected $_name = 'coffre_rune';
	protected $_primary = array('id_coffre_rune', 'id_fk_hobbit_coffre_rune');
	
    function findByIdHobbit($idHobbit, $identifiee = null) {
    	$whereIdentifiee = "";
    	if ($identifiee != null) {
    		$whereIdentifiee = " AND coffre_rune.est_identifiee_rune = '".$identifiee."'";
    	}
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('coffre_rune', '*')
		->from('type_rune', '*')
		->where('id_fk_hobbit_coffre_rune = '.intval($idHobbit))
		->where('coffre_rune.id_fk_type_coffre_rune = type_rune.id_type_rune'.$whereIdentifiee);
		$sql = $select->__toString();

		return $db->fetchAll($sql);
    }
    
    function countByIdHobbit($idHobbit) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('coffre_rune', 'count(*) as nombre')
		->where('id_fk_hobbit_coffre_rune = '.intval($idHobbit));
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		$nombre = $resultat[0]["nombre"];
		return $nombre;
    }
}
