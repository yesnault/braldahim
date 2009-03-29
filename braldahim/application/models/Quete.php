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
class Quete extends Zend_Db_Table {
	protected $_name = 'quete';
	protected $_primary = array('id_quete');

	function findByIdHobbit($idHobbit) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('quete', '*')
		->from('lieu', '*')
		->where('id_fk_lieu_quete = id_lieu')
		->where('id_fk_hobbit_quete = ?', intval($idHobbit))
		->joinLeft('ville','id_fk_ville_lieu = id_ville');
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}
	
	function findEnCoursByIdHobbit($idHobbit) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('quete', '*')
		->from('lieu', '*')
		->where('id_fk_lieu_quete = id_lieu')
		->where('id_fk_hobbit_quete = ?', intval($idHobbit))
		->where('date_fin_quete is null');
		$sql = $select->__toString();

		$result = $db->fetchAll($sql);
		if (count($result) > 1) {
			throw new Zend_Exception("Quete::findEnCoursByIdHobbit nbInvalide:".count($result). " h:".$idHobbit);
		} elseif (count($result) == 1) {
			return $result[0];
		} else {
			return null;
		}
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
