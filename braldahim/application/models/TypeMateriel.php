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
class TypeMateriel extends Zend_Db_Table {
	protected $_name = 'type_materiel';
	protected $_primary = "id_type_materiel";
	
	function findByIdMetier($idMetier) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('type_materiel', '*')
		->where('id_fk_metier_type_materiel = ?',$idMetier)
		->order("nom_type_materiel ASC");
		
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}
}
