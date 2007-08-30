<?php

class Hobbit extends Zend_Db_Table {
	protected $_name = 'hobbit';
	protected $_primary = 'id_hobbit';

	protected $_dependentTables = array('hobbits_competences', 'gardiennage');

	function selectVue($x_min, $y_min, $x_max, $y_max) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('hobbit', '*')
		->where('x_hobbit <= ?',$x_max)
		->where('x_hobbit >= ?',$x_min)
		->where('y_hobbit >= ?',$y_min)
		->where('y_hobbit <= ?',$y_max)
		->where('est_mort_hobbit = ?', "non");
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}

	function findByCase($x, $y, $sansHobbitCourant = -1) {
		$db = $this->getAdapter();
		$select = $db->select();
		if ($sansHobbitCourant != -1) {
			$select->from('hobbit', '*')
			->where('x_hobbit = ?',$x)
			->where('y_hobbit = ?',$y)
			->where('id_hobbit != ?',$sansHobbitCourant)
			->where('est_mort_hobbit = ?', "non");
		} else {
			$select->from('hobbit', '*')
			->where('x_hobbit = ?',$x)
			->where('y_hobbit = ?',$y)
			->where('est_mort_hobbit = ?', "non");
		}
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	public function findById($id){
		$where = $this->getAdapter()->quoteInto('id_hobbit = ?',(int)$id);
		return $this->fetchRow($where);
	}

	public function findByIdList($listId){
		$liste = "";
		foreach($listId as $id) {
			if ($liste == "") {
				$liste = $id;
			} else {
				$liste = $liste." OR id_hobbit=".$id;
			}
		}
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('hobbit', '*')
		->where('id_hobbit ='.$liste);
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	public function findByNom($nom){
		$where = $this->getAdapter()->quoteInto('lcase(nom_hobbit) = ?',(string)strtolower(trim($nom)));
		return $this->fetchRow($where);
	}

	public function findByEmail($email){
		$where = $this->getAdapter()->quoteInto('lcase(email_hobbit) = ?',(string)strtolower(trim($email)));
		return $this->fetchRow($where);
	}

	function findLesPlusProches($x, $y, $rayon, $nombre) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('hobbit', '*, SQRT(((x_hobbit - '.$x.') * (x_hobbit - '.$x.')) + ((y_hobbit - '.$y.') * ( y_hobbit - '.$y.'))) as distance')
		->where('x_hobbit >= ?', $x - $rayon)
		->where('x_hobbit <= ?', $x + $rayon)
		->where('y_hobbit >= ?', $y - $rayon)
		->where('y_hobbit <= ?', $y + $rayon)
		->where('est_mort_hobbit = ?', "non")
		->limit($nombre)
		->order('distance ASC');
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findHobbitAvecRayon($x, $y, $rayon, $idHobbit) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('hobbit', '*')
		->where('x_hobbit >= ?', $x - $rayon)
		->where('x_hobbit <= ?', $x + $rayon)
		->where('y_hobbit >= ?', $y - $rayon)
		->where('y_hobbit <= ?', $y + $rayon)
		->where('est_mort_hobbit = ?', "non")
		->where('id_hobbit = ?', $idHobbit);
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
	function findHobbitsParNom($nom) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('hobbit', '*')
		->where('lcase(nom_hobbit) like ?', (string)strtolower(trim($nom)));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}

