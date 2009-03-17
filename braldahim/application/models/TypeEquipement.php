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
class TypeEquipement extends Zend_Db_Table {
	protected $_name = 'type_equipement';
	protected $_primary = "id_type_equipement";
	
	function findByIdMetier($idMetier, $ordre) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('type_equipement', '*')
		->where('id_fk_metier_type_equipement = ?',$idMetier)
		->order($ordre);
		
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}
}
