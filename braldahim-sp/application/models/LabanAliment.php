<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: LabanAliment.php 2234 2009-12-08 22:51:07Z yvonnickesnault $
 * $Author: yvonnickesnault $
 * $LastChangedDate: 2009-12-08 23:51:07 +0100 (mar., 08 déc. 2009) $
 * $LastChangedRevision: 2234 $
 * $LastChangedBy: yvonnickesnault $
 */
class LabanAliment extends Zend_Db_Table {
	protected $_name = 'laban_aliment';
	protected $_primary = array('id_laban_aliment');

	function findByIdHobbit($idHobbit, $typeAliment = null) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('laban_aliment', '*')
		->from('type_aliment')
		->from('type_qualite')
		->from('aliment', '*')
		->where('id_aliment = id_laban_aliment')
		->where('id_fk_type_aliment = id_type_aliment')
		->where('id_fk_type_qualite_aliment = id_type_qualite')
		->where('id_fk_hobbit_laban_aliment = ?', intval($idHobbit));
		if ($typeAliment != null) {
			$select->where('type_type_aliment = ?', $typeAliment);
		}
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
    function countByIdHobbit($idHobbit) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('laban_aliment', 'count(*) as nombre')
		->where('id_fk_hobbit_laban_aliment = '.intval($idHobbit));
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		$nombre = $resultat[0]["nombre"];
		return $nombre;
    }
}
