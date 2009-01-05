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
class Creation extends Zend_Db_Table {
	protected $_name = 'creation';
	protected $_primary = array('id_creation');

	function findByType($type) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('creation', '*')
		->where('type_creation = ?', $type);
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}
	
	function findDernierByType($type = null) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('creation', array('max(date_creation) as date_creation'));
		if ($type != null) {
			$select->where('type_creation  = ?', $type);
		}
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);
	
		if ($type != null&& count($resultat) != 1) {
			throw new Zend_Exception("count invalide:".count($resultat). " type:".$type);
		}
		
		$select = $db->select();
		$select->from('creation', '*');
		if ($type != null) {
			$select->where('type_creation  = ?', $type);
		}
		$select->where('date_creation = ?', $resultat[0]["date_creation"]);
		
		$sql = $select->__toString();
		
		return $db->fetchAll($sql);
	}
	
	public function findDistinctDate() {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('creation', 'distinct(date_creation) as date_creation')
		->order(array('date_creation DESC'));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
}
