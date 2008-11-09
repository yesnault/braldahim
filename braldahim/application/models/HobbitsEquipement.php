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
class HobbitsEquipement extends Zend_Db_Table {
    protected $_name = 'hobbits_equipement';
	
    function findByIdHobbit($id_hobbit) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('hobbits_equipement', '*')
		->from('type_equipement', '*')
		->where('hobbits_equipement.id_fk_hobbit_hequipement = '.intval($id_hobbit))
		->where('hobbits_equipement.id_fk_type_hequipement = type_equipement.id_type_equipement');
		$sql = $select->__toString();
		return $db->fetchAll($sql);
    }
}