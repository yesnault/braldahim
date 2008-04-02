<?php

class HobbitCommunaute extends Zend_Db_Table {
	protected $_name = 'hobbits_communaute';
	protected $_primary = array('id_fk_communaute_communaute', 'id_fk_hobbit_communaute');

	public function findByIdHobbit($idHobbit) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('hobbits_communaute', '*')
		->from('communaute')
		->from('rang_communaute')
		->where('id_fk_communaute_communaute = id_communaute')
		->where('id_fk_rang_communaute_hobbit_communaute = id_fk_type_rang_communaute')
		->where('id_fk_hobbit_communaute = ?', intval($idHobbit));
		
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
	public function findByIdCommunaute($idCommunaute, $idRang, $page, $nbMax, $ordre, $sens) {
			
		if ($idRang != -1) {
			$and = " AND id_fk_rang_communaute_hobbit_communaute = ".intval($idRang); 
		} else {
			$and = "";
		}
		
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('hobbits_communaute', '*')
		->from('communaute')
		->from('rang_communaute')
		->from('hobbit')
		->where('id_fk_communaute_communaute = ?', intval($idCommunaute))
		->where('id_fk_rang_communaute_hobbit_communaute = id_fk_type_rang_communaute')
		->where('id_fk_communaute_rang_communaute = id_fk_communaute_communaute	')
		->where('id_fk_hobbit_communaute = id_hobbit')
		->where("id_communaute = id_fk_communaute_communaute".$and)
		->order($ordre.$sens)
		->limitPage($page, $nbMax);
		
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
	public function countByIdCommunaute($idCommunaute) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('hobbits_communaute', 'count(*) as nombre')
		->where('id_fk_communaute_communaute = ?', intval($idCommunaute));
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		$nombre = $resultat[0]["nombre"];
		return $nombre;
	}
}
