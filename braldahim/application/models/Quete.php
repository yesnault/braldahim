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
class Quete extends Zend_Db_Table {
	protected $_name = 'quete';
	protected $_primary = array('id_quete');

	function findByIdHobbit($idHobbit) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('quete', '*')
		->from('lieu', '*')
		->where('id_fk_lieu_quete = id_lieu')
		->where('id_fk_hobbit_quete = ?', intval($idHobbit));
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}
	
	function findByIdHobbitAndIdQuete($idHobbit, $idQuete) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('quete', '*')
		->from('lieu', '*')
		->where('id_fk_lieu_quete = id_lieu')
		->where('id_fk_hobbit_quete = ?', intval($idHobbit))
		->where('id_quete = ?', intval($idQuete));
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}
	
	function findByIdHobbitAndIdLieu($idHobbit, $idLieu) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('quete', '*')
		->from('lieu', '*')
		->where('id_fk_lieu_quete = id_lieu')
		->where('id_fk_hobbit_quete = ?', intval($idHobbit))
		->where('id_fk_lieu_quete = ?', intval($idLieu));
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}
}
