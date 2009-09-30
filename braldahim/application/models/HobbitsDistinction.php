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
class HobbitsDistinction extends Zend_Db_Table {
	protected $_name = 'hobbits_distinction';
	protected $_primary = array('id_hdistinction');

	function findDistinctionsByHobbitId($idHobbit) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('hobbits_distinction', '*')
		->from('type_distinction', '*')
		->where('id_fk_hobbit_hdistinction = ? ', intval($idHobbit))
		->where('id_fk_type_distinction_hdistinction = id_type_distinction')
		->order('date_hdistinction');
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findDistinctionsByHobbitIdAndIdTypeDistinction($idHobbit, $idTypeDistinction) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('hobbits_distinction', '*')
		->from('type_distinction', '*')
		->where('id_fk_hobbit_hdistinction = ? ', intval($idHobbit))
		->where('id_fk_type_distinction_hdistinction = id_type_distinction')
		->where('id_type_distinction = ?', intval($idTypeDistinction))
		->order('date_hdistinction');
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function countDistinctionByIdHobbitList($listId, $idTypeDistinction) {

		$nomChamp = "id_fk_hobbit_hdistinction";
		$liste = "";
		foreach($listId as $id) {
			if ((int) $id."" == $id."") {
				if ($liste == "") {
					$liste = $id;
				} else {
					$liste = $liste." OR ".$nomChamp."=".$id;
				}
			}
		}
			
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('hobbits_distinction', array('count(*) as nombre', 'id_fk_hobbit_hdistinction'))
		->where('id_fk_type_distinction_hdistinction = ?', intval($idTypeDistinction))
		->where('id_fk_hobbit_hdistinction = '.$liste)
		->group('id_fk_hobbit_hdistinction');
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}