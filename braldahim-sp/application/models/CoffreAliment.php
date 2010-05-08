<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: CoffreAliment.php 2225 2009-12-07 12:01:27Z yvonnickesnault $
 * $Author: yvonnickesnault $
 * $LastChangedDate: 2009-12-07 13:01:27 +0100 (lun., 07 déc. 2009) $
 * $LastChangedRevision: 2225 $
 * $LastChangedBy: yvonnickesnault $
 */
class CoffreAliment extends Zend_Db_Table {
	protected $_name = 'coffre_aliment';
	protected $_primary = array('id_coffre_aliment');

	function findByIdBraldun($idBraldun) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('coffre_aliment', '*')
		->from('type_aliment')
		->from('type_qualite')
		->from('aliment', '*')
		->where('id_aliment = id_coffre_aliment')
		->where('id_fk_type_aliment = id_type_aliment')
		->where('id_fk_type_qualite_aliment = id_type_qualite')
		->where('id_fk_braldun_coffre_aliment = ?', intval($idBraldun));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
    function countByIdBraldun($idBraldun) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('coffre_aliment', 'count(*) as nombre')
		->where('id_fk_braldun_coffre_aliment = '.intval($idBraldun));
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		$nombre = $resultat[0]["nombre"];
		return $nombre;
    }
}
