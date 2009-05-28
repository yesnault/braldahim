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
class VenteMunition extends Zend_Db_Table {
	protected $_name = 'vente_munition';
	protected $_primary = array('id_vente_munition');

	function findByIdVente($idVente) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('vente_munition', '*')
		->from('type_munition', '*')
		->where('id_fk_vente_munition = '.intval($idVente))
		->where('vente_munition.id_fk_type_vente_munition = type_munition.id_type_munition');
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}
}
