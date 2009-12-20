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
class RangCommunaute extends Zend_Db_Table {
	protected $_name = 'rang_communaute';
	protected $_primary = array('id_rang_communaute');

	function findByIdCommunaute($idCommunaute) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('rang_communaute')
		->where('id_fk_communaute_rang_communaute = ?', intval($idCommunaute))
		->order('ordre_rang_communaute');

		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findRangCreateur($idCommunaute) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('rang_communaute', '*')
		->where('id_fk_communaute_rang_communaute = ?', intval($idCommunaute))
		->where('ordre_rang_communaute = 1');

		$sql = $select->__toString();
		return $db->fetchRow($sql);
	}

	function findRangSecond($idCommunaute) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('rang_communaute', '*')
		->where('id_fk_communaute_rang_communaute = ?', intval($idCommunaute))
		->where('ordre_rang_communaute = 2');

		$sql = $select->__toString();
		return $db->fetchRow($sql);
	}
	
	function findAll() {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('rang_communaute', '*')
		->order(array('id_fk_communaute_rang_communaute ASC', 'ordre_rang_communaute ASC'));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findRangNouveau($idCommunaute) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('rang_communaute', '*')
		->where('id_fk_communaute_rang_communaute = ?', intval($idCommunaute))
		->where('ordre_rang_communaute = 20');

		$sql = $select->__toString();
		return $db->fetchRow($sql);
	}
}
