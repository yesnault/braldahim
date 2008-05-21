<?php

class Hobbit extends Zend_Db_Table {
	protected $_name = 'hobbit';
	protected $_primary = 'id_hobbit';

	protected $_dependentTables = array('hobbits_competences', 'gardiennage');

	function findAll($page, $nbMax) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('hobbit', '*')
		->order(array('nom_hobbit', 'prenom_hobbit'))
		->limitPage($page, $nbMax);
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
	function selectVue($x_min, $y_min, $x_max, $y_max, $sansHobbitCourant = -1) {
		$db = $this->getAdapter();
		$select = $db->select();
		if ($sansHobbitCourant != -1) {
			$select->from('hobbit', '*')
			->where('x_hobbit <= ?',$x_max)
			->where('x_hobbit >= ?',$x_min)
			->where('y_hobbit >= ?',$y_min)
			->where('y_hobbit <= ?',$y_max)
			->where('est_mort_hobbit = ?', "non")
			->where('id_hobbit != ?',$sansHobbitCourant);
		} else {
			$select->from('hobbit', '*')
			->where('x_hobbit <= ?',$x_max)
			->where('x_hobbit >= ?',$x_min)
			->where('y_hobbit >= ?',$y_min)
			->where('y_hobbit <= ?',$y_max)
			->where('est_mort_hobbit = ?', "non")
			->joinLeft('communaute','id_fk_communaute_hobbit = id_communaute');;
		}
		
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
		return $this->findByList("id_hobbit", $listId);
	}
	
	public function findByIdFkJosUsersList($listId){
		return $this->findByList("id_fk_jos_users_hobbit", $listId);
	}
	
	private function findByList($nomChamp, $listId) {
		$liste = "";
		foreach($listId as $id) {
			if ((int) $id."" == $id."") {
				if ($liste == "") {
					$liste = $id;
				} else {
					$liste = $liste." OR id_fk_jos_users_hobbit=".$id;
				}
			}
		}
		
		if ($liste != "") {
			$db = $this->getAdapter();
			$select = $db->select();
			$select->from('hobbit', '*')
			->where('id_fk_jos_users_hobbit ='.$liste);
			$sql = $select->__toString();
			return $db->fetchAll($sql);
		} else {
			return null;
		}
	}
	
	public function findByIdNomInitialPrenom($idNom, $prenom){
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('hobbit', '*')
		->where('id_fk_nom_initial_hobbit = ?', $idNom)
		->where('lcase(prenom_hobbit) = ?', (string)strtolower(trim($prenom)));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	public function findByEmail($email){
		$where = $this->getAdapter()->quoteInto('lcase(email_hobbit) = ?',(string)strtolower(trim($email)));
		return $this->fetchRow($where);
	}

	function findLesPlusProches($x, $y, $rayon, $nombre, $idMonstre = null) {
		$and = "";
		if ($idMonstre != null) {
			$and = " AND id_fk_type_monstre_effet_mot_f != ".(int)$idMonstre;
		}

		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('hobbit', '*, SQRT(((x_hobbit - '.$x.') * (x_hobbit - '.$x.')) + ((y_hobbit - '.$y.') * ( y_hobbit - '.$y.'))) as distance')
		->where('x_hobbit >= ?', $x - $rayon)
		->where('x_hobbit <= ?', $x + $rayon)
		->where('y_hobbit >= ?', $y - $rayon)
		->where('y_hobbit <= ?', $y + $rayon)
		->where("est_mort_hobbit = 'non' ".$and)
		->joinLeft('effet_mot_f','id_fk_hobbit_effet_mot_f = id_hobbit')
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
	
	function findHobbitsParNomPrenom($nom, $prenom) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('hobbit', '*')
		->where('lcase(nom_hobbit) like ?', (string)strtolower(trim($nom)))
		->where('lcase(prenom_hobbit) like ?', (string)strtolower(trim($prenom)));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
	function findHobbitsMasculinSansConjoint($idHobbit) {
		$db = $this->getAdapter();
		$select = $db->select();
		$sql = "SELECT id_hobbit FROM hobbit WHERE sexe_hobbit='masculin' AND id_hobbit <> ".(int)$idHobbit." AND id_hobbit NOT IN (SELECT id_fk_m_hobbit_couple FROM couple)";
		return $db->fetchAll($sql);
	}
	
	function findHobbitsFemininSansConjoint($idHobbit) {
		$db = $this->getAdapter();
		$select = $db->select();
		$sql = "SELECT id_hobbit FROM hobbit WHERE sexe_hobbit='feminin' AND id_hobbit <> ".(int)$idHobbit." AND id_hobbit NOT IN (SELECT id_fk_f_hobbit_couple FROM couple)";
		return $db->fetchAll($sql);
	}

	function findEnfants($sexe, $idHobbit) {
		$db = $this->getAdapter();
		$select = $db->select();
		if ($sexe == "masculin") {
			$select->from('hobbit', '*')
			->where('id_fk_pere_hobbit = ?', (int)$idHobbit);
		} else {
			$select->from('hobbit', '*')
			->where('id_fk_mere_hobbit = ?', (int)$idHobbit);
		}
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
	public function findByIdCommunaute($idCommunaute, $idRang = -1 , $page = null, $nbMax = null, $ordre = null, $sens = null) {
		if ($idRang != -1) {
			$and = " AND id_fk_rang_communaute_hobbit = ".intval($idRang); 
		} else {
			$and = "";
		}
		
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('hobbit')
		->from('communaute')
		->from('rang_communaute')
		->where('id_fk_communaute_hobbit = ?', intval($idCommunaute))
		->where('id_fk_rang_communaute_hobbit = id_rang_communaute')
		->where('id_rang_communaute = id_fk_rang_communaute_hobbit')
		->where("id_communaute = id_fk_communaute_hobbit".$and);
		
		if ($ordre != null && $sens != null) {
			$select->order($ordre.$sens);
		} else {
			$select->order("prenom_hobbit");
		}
		
		if ($page != null && $nbMax != null) {
			$select->limitPage($page, $nbMax);
		}
		
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
	public function countByIdCommunaute($idCommunaute) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('hobbit', 'count(*) as nombre')
		->where('id_fk_communaute_hobbit = ?', intval($idCommunaute));
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		$nombre = $resultat[0]["nombre"];
		return $nombre;
	}
	
}

