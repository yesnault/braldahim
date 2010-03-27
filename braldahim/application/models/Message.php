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
class Message extends Zend_Db_Table {
	protected $_name = 'message';
	protected $_primary = 'id';

	public function findById($idUser, $id) {
		$db = $this->getAdapter();
		$select = $db->select();
		
		$select->from('message', '*')
		->where('message.id = '.intval($id))
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
	
	public function findByToId($toId, $page, $nbMax, $toread = null) {
		$db = $this->getAdapter();
		$select = $db->select();
		
		$select->from('message', '*')
		->where('message.toid = '.intval($toId))
		->where('message.totrash = 0')
		->order('datum DESC')
		->limitPage($page, $nbMax);
		
		if ($toread != null && $toread === true) {
			$select->where('toread = 0');
		}
		
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	public function findByFromId($toId, $page, $nbMax) {
		$db = $this->getAdapter();
		$select = $db->select();
		
		$select->from('message', '*')
		->where('message.fromid = '.intval($toId))
		->where('message.totrashoutbox = 0')
		->order('datum DESC')
		->limitPage($page, $nbMax);
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
	public function findByToOrFromIdSupprime($toOrFromId, $page, $nbMax) {
		$db = $this->getAdapter();
		$select = $db->select();
		
		$select->from('message', '*')
		->where('(message.toid = '.intval($toOrFromId). ' AND message.totrash = 1) OR (message.fromid = '.intval($toOrFromId).' AND message.totrashoutbox = 1)')
		->order('datum DESC')
		->limitPage($page, $nbMax);
		$sql = $select->__toString();
		return $db->fetchAll($sql);
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
}