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
class VenteMinerai extends Zend_Db_Table {
	protected $_name = 'vente_minerai';
	protected $_primary = array('id_vente_minerai');

	function findByIdVente($idVente) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('vente_minerai', '*')
		->from('type_minerai', '*')
		->where('vente_minerai.id_fk_type_vente_minerai = type_minerai.id_type_minerai')
		->where('id_fk_vente_minerai = ?', (int)$idVente);
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

}
