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
class Etape extends Zend_Db_Table {
	protected $_name = 'etape';
	protected $_primary = array('id_etape');

	function findByIdHobbitAndIdTypeEtape($idHobbit, $idTypeEtape) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('etape', '*')
		->where('id_fk_hobbit_etape = ?', intval($idHobbit))
		->where('id_fk_type_etape = ?', intval($idTypeEtape));
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}
	
	function findByIdQuete($idQuete) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('etape', '*')
		->where('id_fk_quete_etape = ?', intval($idQuete));
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}
}
