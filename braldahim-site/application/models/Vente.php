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
class Vente extends Zend_Db_Table {
	protected $_name = 'vente';
	protected $_primary = array('id_vente');

	function findByIdVente($idVente) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('vente', '*')
		->where('id_vente = '.intval($idVente));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
	function findByIdBraldun($idBraldun) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('vente', '*')
		->where('id_fk_braldun_vente = '.intval($idBraldun));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
	function findDernieres($nb) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('vente', '*')
		->order('id_vente desc')
		->limit($nb, 0);
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
	function findATerme($nb) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('vente', '*')
		->order('date_fin_vente desc')
		->limit($nb, 0);
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
	function findAllByType($typeVente) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('vente', '*')
		->where('type_vente = ?', $typeVente);
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}