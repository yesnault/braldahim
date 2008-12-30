<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: session.php 839 2008-12-26 21:35:54Z yvonnickesnault $
 * $Author: yvonnickesnault $
 * $LastChangedDate: 2008-12-26 22:35:54 +0100 (Fri, 26 Dec 2008) $
 * $LastChangedRevision: 839 $
 * $LastChangedBy: yvonnickesnault $
 */
class Session extends Zend_Db_Table {
	protected $_name = 'session';
	protected $_primary = array('id_fk_hobbit_session');

	function countByIdHobbitAndIdSession($idSession, $idSessionPhp) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('session', 'count(*) as nombre')
		->where('id_fk_hobbit_session = ?', $idSession)
		->where('id_php_session = ?', $idSessionPhp);
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);
		return $resultat[0]["nombre"];
	}

	function insertOrUpdate($data) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('session', 'count(*) as nombre')
		->where('id_fk_hobbit_session = ?',$data["id_fk_hobbit_session"]);
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);
		
		if ($resultat[0]["nombre"] == 0) { // insert
			$this->insert($data);
		} else { // update
			$where = 'id_fk_hobbit_session = '.$data["id_fk_hobbit_session"];
			$this->update($data, $where);
		}
	}
}
