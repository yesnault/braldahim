<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id:$
 * $Author:$
 * $LastChangedDate:$
 * $LastChangedRevision:$
 * $LastChangedBy:$
 */
class Message extends Zend_Db_Table {
	protected $_name = 'message';
	protected $_primary = 'id_message';

	public function findByIdHobbit($idHobbit, $idType, $page, $nbMax) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('message', '*')
		->where('message.id_fk_type_message = '.intval($idType))
		->where('message.id_fk_hobbit_message = '.intval($idHobbit))
		->order('date_envoi_message DESC')
		->limitPage($page, $nbMax);
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	public function findByIdHobbitAndIdMessage($idHobbit, $idMessage) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('message', '*')
		->where('message.id_message = '.intval($idMessage))
		->where('message.id_fk_hobbit_message = '.intval($idHobbit));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}