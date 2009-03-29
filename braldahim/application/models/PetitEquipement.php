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
class PetitEquipement extends Zend_Db_Table {
	protected $_name = 'petit_equipement';
	protected $_primary = array('id_petit_equipement');
	
    function findByIdMetier($idMetier) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('petit_equipement', '*')
		->where('id_fk_metier_petit_equipement = ?', (int)$idMetier)
		->order('id_petit_equipement');
		$sql = $select->__toString();

		return $db->fetchAll($sql);
    }
}
