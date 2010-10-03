<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Message extends Zend_Db_Table {
	protected $_name = 'message';
	protected $_primary = 'id';

	public function findById($idUser, $id) {
		$db = $this->getAdapter();
		$select = $db->select();

		$select->from('message', '*')
		->where('message.id = ?', intval($id))
		->where('message.toid = '.intval($idUser). ' OR message.fromid = '.intval($idUser));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	public function findByIdList($idUser, $listId) {

		$liste = "";
		$nomChamp = "id";
		if (count($listId) < 1) {
			$liste = "";
		} else {
			foreach($listId as $id) {
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
			$db = $this->getAdapter();
			$select = $db->select();

			$select->from('message', '*')
			->where('message.toid = '.intval($idUser). ' OR message.fromid = '.intval($idUser))
			->where($nomChamp ."=". $liste);
			$sql = $select->__toString();
			return $db->fetchAll($sql);
		} else {
			return null;
		}
	}

	public function getAllWithSelect($select) {
		$db = $this->getAdapter();
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	public function getSelectByToId($toId, $toread = null) {
		$db = $this->getAdapter();
		$select = $db->select();

		$select->from('message', '*')
		->where('message.toid = '.intval($toId))
		->where('message.totrash = 0')
		->where('message.archived = 0')
		->order('date_message DESC');

		if ($toread != null && $toread === true) {
			$select->where('toread = 0');
		}
		return $select;
	}

	public function getSelectByFromId($toId) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('message', '*')
		->where('message.fromid = ?', intval($toId))
		->where('message.totrashoutbox = 0')
		->order('date_message DESC');
		return $select;
	}

	public function getSelectByToOrFromIdSupprime($toOrFromId) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('message', '*')
		->where('(message.toid = '.intval($toOrFromId). ' AND message.totrash = 1) OR (message.fromid = '.intval($toOrFromId).' AND message.totrashoutbox = 1)')
		->order('date_message DESC');
		return $select;
	}

	public function getSelectByToIdArchive($toId) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('message', '*')
		->where('message.archived = 1 AND message.toid = ? AND message.totrash = 0', intval($toId))
		->order('date_message DESC');
		return $select;
	}

	public function countByToIdNotRead($id) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('message', 'count(*) as nombre')
		->where('message.toid = '.intval($id). ' AND message.toread = 0 AND message.totrash = 0');
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);
		$nombre = $resultat[0]["nombre"];
		return $nombre;
	}

	public function countByToIdArchived($id) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('message', 'count(*) as nombre')
		->where('message.toid = ? AND message.archived = 1 AND message.totrash = 0', intval($id));
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);
		$nombre = $resultat[0]["nombre"];
		return $nombre;
	}
}