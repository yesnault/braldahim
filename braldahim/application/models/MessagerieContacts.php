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
class MessagerieContacts extends Zend_Db_Table {
	protected $_name = 'messagerie_contacts';
	protected $_primary = 'id';

	public function findByUserId($userId) {
		$db = $this->getAdapter();
		$select = $db->select();

		$select->from('messagerie_contacts', '*')
		->where('messagerie_contacts.userid = '.intval($userId))
		->order('messagerie_contacts.name');
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	public function findByIdList($idList, $userId) {
		$where = 'messagerie_contacts.id = '.intval($idList);
		$where .= ' AND messagerie_contacts.userid = '.intval($userId);
		return $this->fetchRow($where);
	}

	public function findByIdsList($listIds, $userId) {
		return $this->findByList("messagerie_contacts.id", $listIds, $userId);
	}

	private function findByList($nomChamp, $listIds, $userId) {
		$liste = "";
		if (count($listIds) < 1) {
			$liste = "";
		} else {
			foreach($listIds as $id) {
				if ((int) $id."" == $id."") {
					if ($liste == "") {
						$liste = $id;
					} else {
						$liste = $liste." OR ".$nomChamp."=".$id;
					}
				}
			}
		}

		if ($liste != "") {
			$liste = $liste . ' AND messagerie_contacts.userid = '.intval($userId);
		} else {
			$liste = 'messagerie_contacts.userid = '.intval($userId);
		}

		if ($liste != "") {
			$db = $this->getAdapter();
			$select = $db->select();
			$select->from('messagerie_contacts', '*')
			->where($nomChamp .'='. $liste);
			$sql = $select->__toString();
			return $db->fetchAll($sql);
		} else {
			return null;
		}
	}
}