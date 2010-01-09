<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: CharretteAliment.php 2225 2009-12-07 12:01:27Z yvonnickesnault $
 * $Author: yvonnickesnault $
 * $LastChangedDate: 2009-12-07 13:01:27 +0100 (lun., 07 déc. 2009) $
 * $LastChangedRevision: 2225 $
 * $LastChangedBy: yvonnickesnault $
 */
class CharretteAliment extends Zend_Db_Table {
	protected $_name = 'charrette_aliment';
	protected $_primary = array('id_charrette_aliment');

	function findByIdCharrette($idCharrette) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('charrette_aliment', '*')
		->from('type_aliment')
		->from('type_qualite')
		->from('aliment', '*')
		->where('id_aliment = id_charrette_aliment')
		->where('id_fk_type_aliment = id_type_aliment')
		->where('id_fk_type_qualite_aliment = id_type_qualite')
		->where('id_fk_charrette_aliment = ?', intval($idCharrette));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
    function countByIdCharrette($idCharrette) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('charrette_aliment', 'count(*) as nombre')
		->where('id_fk_charrette_aliment = '.intval($idCharrette));
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		$nombre = $resultat[0]["nombre"];
		return $nombre;
    }
}
