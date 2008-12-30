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

	function findAll() {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('session', '*')
		->from('hobbit', '*')
		->where('id_fk_hobbit_session = id_hobbit');
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
	function count() {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('session', 'count(*) as nombre');
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);
		return $resultat[0]["nombre"];
	}
	
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
	
	function purge() {
		$where = "date_derniere_action_session < '".Bral_Util_ConvertDate::get_date_add_time_to_date(date("Y-m-d H:i:s"), "00:-15:00")."'";
		$this->delete($where);
	}
}
