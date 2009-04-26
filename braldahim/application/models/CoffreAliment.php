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
class CoffreAliment extends Zend_Db_Table {
	protected $_name = 'coffre_aliment';
	protected $_primary = array('id_coffre_aliment');

	function findByIdHobbit($idHobbit) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('coffre_aliment', '*')
		->from('type_aliment')
		->from('type_qualite')
		->where('id_fk_type_coffre_aliment = id_type_aliment')
		->where('id_fk_type_qualite_coffre_aliment = id_type_qualite')
		->where('id_fk_hobbit_coffre_aliment = ?', intval($idHobbit));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
    function countByIdHobbit($idHobbit) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('coffre_aliment', 'count(*) as nombre')
		->where('id_fk_hobbit_coffre_aliment = '.intval($idHobbit));
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		$nombre = $resultat[0]["nombre"];
		return $nombre;
    }
}
