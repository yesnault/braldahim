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
class Hobbit extends Zend_Db_Table {
	protected $_name = 'hobbit';
	protected $_primary = 'id_hobbit';

	protected $_dependentTables = array('hobbits_competences', 'gardiennage');

	function findAll($page, $nbMax) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('hobbit', '*');
		$select->order(array('nom_hobbit', 'prenom_hobbit'));
		$select->limitPage($page, $nbMax);
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findByCriteres($niveau = -1 , $page = null, $nbMax = null, $ordre = null, $sens = null, $where = null) {
		if ($niveau != -1) {
			$and = " niveau_hobbit = ".intval($niveau);
		} else {
			$and = null;
		}

		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('hobbit')
		->where('est_compte_actif_hobbit = ?', "oui")
		->where('est_en_hibernation_hobbit = ?', "non");

		if ($and != null) {
			$select->where($and);
		}

		if ($ordre != null && $sens != null) {
			$select->order($ordre.$sens);
		} else {
			$select->order("prenom_hobbit");
		}

		if ($page != null && $nbMax != null) {
			$select->limitPage($page, $nbMax);
		}

		if ($where != null) {
			$select->where($where);
		}

		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findDistinctNiveau() {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('hobbit', 'distinct(niveau_hobbit) as niveau_hobbit');
		$select->order('niveau_hobbit');
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function selectVue($x_min, $y_min, $x_max, $y_max, $z, $sansHobbitCourant = -1, $avecIntangibles = true) {
		$db = $this->getAdapter();
		$select = $db->select();

		$select->from('hobbit', '*');
		$select->where('x_hobbit <= ?',$x_max);
		$select->where('x_hobbit >= ?',$x_min);
		$select->where('y_hobbit >= ?',$y_min);
		$select->where('y_hobbit <= ?',$y_max);
		$select->where('z_hobbit = ?',$z);
		$select->where('est_ko_hobbit = ?', "non");
		$select->where('est_compte_actif_hobbit = ?', "oui");
		$select->where('est_en_hibernation_hobbit = ?', "non");
		$select->where('est_pnj_hobbit = ?', "non");

		if ($avecIntangibles == false) {
			$select->where("est_intangible_hobbit like ?", "non");
		}
			
		if ($sansHobbitCourant != -1) {
			$select->where('id_hobbit != ?',$sansHobbitCourant);
		}
		$select->joinLeft('communaute','id_fk_communaute_hobbit = id_communaute');

		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findByCase($x, $y, $z, $sansHobbitCourant = -1, $avecIntangibles = true) {
		$db = $this->getAdapter();
		$select = $db->select();

		$select->from('hobbit', '*');
		$select->where('x_hobbit = ?',$x);
		$select->where('y_hobbit = ?',$y);
		$select->where('z_hobbit = ?',$z);
		$select->where('est_compte_actif_hobbit = ?', "oui");
		$select->where('est_en_hibernation_hobbit = ?', "non");
		$select->where('est_ko_hobbit = ?', "non");
		$select->where('est_pnj_hobbit = ?', "non");

		if ($sansHobbitCourant != -1) {
			$select->where('id_hobbit != ?',$sansHobbitCourant);
		}

		if ($avecIntangibles == false) {
			$select->where("est_intangible_hobbit like ?", "non");
		}

		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	public function findById($id){
		$where = $this->getAdapter()->quoteInto('id_hobbit = ?',(int)$id);
		return $this->fetchRow($where);
	}

	function findNomById($id) {
		$where = $this->getAdapter()->quoteInto('id_hobbit = ?',(int)$id);
		$hobbit = $this->fetchRow($where);

		if ($hobbit == null) {
			$retour = "hobbit inconnu";
		} else {
			$retour = $hobbit["prenom_hobbit"]. " ".$hobbit["nom_hobbit"]. " (".$hobbit["id_hobbit"].")";
		}
		return $retour;
	}

	public function findByIdList($listId){
		return $this->findByList("id_hobbit", $listId);
	}

	private function findByList($nomChamp, $listId) {
		$liste = "";
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
			$select->from('hobbit', '*')
			->where($nomChamp .'='. $liste);
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
		->where('lcase(prenom_hobbit) like ?', (string)mb_strtolower(trim($prenom)));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	public function findByEmail($email){
		$where = $this->getAdapter()->quoteInto('lcase(email_hobbit) = ?',(string)mb_strtolower(trim($email)));
		return $this->fetchRow($where);
	}

	function findLesPlusProches($x, $y, $z, $rayon, $nombre, $idTypeMonstre = null, $avecIntangibles = true) {
		$and = "";
		if ($idTypeMonstre != null) {
			$and = " AND id_fk_type_monstre_effet_mot_f != ".(int)$idTypeMonstre;
		}

		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('hobbit', '*, SQRT(((x_hobbit - '.$x.') * (x_hobbit - '.$x.')) + ((y_hobbit - '.$y.') * ( y_hobbit - '.$y.'))) as distance')
		->where('x_hobbit >= ?', $x - $rayon)
		->where('x_hobbit <= ?', $x + $rayon)
		->where('y_hobbit >= ?', $y - $rayon)
		->where('y_hobbit <= ?', $y + $rayon)
		->where('z_hobbit = ?', $z)
		->where("est_ko_hobbit = 'non'")
		->where('est_compte_actif_hobbit = ?', "oui")
		->where('est_en_hibernation_hobbit = ?', "non")
		->where('est_pnj_hobbit = ?', "non");

		if ($avecIntangibles == false) {
			$select->where("est_intangible_hobbit like ?", "non");
		}

		$select->joinLeft('effet_mot_f','id_fk_hobbit_effet_mot_f = id_hobbit')
		->limit($nombre)
		->order(array('distance ASC','niveau_hobbit ASC'));

		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findHobbitAvecRayon($x, $y, $rayon, $idHobbit, $avecIntangibles) {

		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('hobbit', '*')
		->where('x_hobbit >= ?', $x - $rayon)
		->where('x_hobbit <= ?', $x + $rayon)
		->where('y_hobbit >= ?', $y - $rayon)
		->where('y_hobbit <= ?', $y + $rayon)
		->where('est_ko_hobbit = ?', "non")
		->where('est_compte_actif_hobbit = ?', "oui")
		->where('est_en_hibernation_hobbit = ?' ,"non")
		->where('est_pnj_hobbit = ?', "non")
		->where('id_hobbit = ?', $idHobbit);

		if ($avecIntangibles == false) {
			$select->where("est_intangible_hobbit like ?", "non");
		}

		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findHobbitsParNomPrenom($nom, $prenom) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('hobbit', '*')
		->where('lcase(nom_hobbit) like ?', (string)mb_strtolower(trim($nom)))
		->where('lcase(prenom_hobbit) like ?', (string)mb_strtolower(trim($prenom)));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findHobbitsParPrenom($prenom) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('hobbit', '*')
		->where('lcase(prenom_hobbit) like ?', (string)mb_strtolower(trim($prenom)));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findHobbitsParPrenomAndIdTypeDistinction($prenom, $idTypeDistinction) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('hobbit', '*')
		->from('hobbits_distinction', null)
		->where('id_fk_type_distinction_hdistinction = ?', intval($idTypeDistinction))
		->where('id_fk_hobbit_hdistinction = id_hobbit')
		->where('lcase(prenom_hobbit) like ?', (string)mb_strtolower(trim($prenom)));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findByIdListAndIdTypeDistinction($listId, $idTypeDistinction) {

		$liste = "";
		$nomChamp = "id_hobbit";
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
			$select->from('hobbit', '*')
			->from('hobbits_distinction', null)
			->where('id_fk_type_distinction_hdistinction = ?', intval($idTypeDistinction))
			->where('id_fk_hobbit_hdistinction = id_hobbit')
			->where($nomChamp .'='. $liste);
			$sql = $select->__toString();
			return $db->fetchAll($sql);
		} else {
			return null;
		}
	}

	function findHobbitsMasculinSansConjoint($idHobbit) {
		$db = $this->getAdapter();
		$sql = "SELECT id_hobbit, nom_hobbit, prenom_hobbit, niveau_hobbit FROM hobbit WHERE sexe_hobbit='masculin' AND est_compte_actif_hobbit='oui' AND est_pnj_hobbit='non' AND id_hobbit <> ".(int)$idHobbit." AND id_hobbit NOT IN (SELECT id_fk_m_hobbit_couple FROM couple)";
		return $db->fetchAll($sql);
	}

	function findHobbitsFemininSansConjoint($idHobbit) {
		$db = $this->getAdapter();
		$sql = "SELECT id_hobbit, nom_hobbit, prenom_hobbit, niveau_hobbit FROM hobbit WHERE sexe_hobbit='feminin' AND est_compte_actif_hobbit='oui' AND est_pnj_hobbit='non' AND id_hobbit <> ".(int)$idHobbit." AND id_hobbit NOT IN (SELECT id_fk_f_hobbit_couple FROM couple)";
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

	function findAllBatchByDateFin($dateFin) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('hobbit', '*')
		->where('est_compte_actif_hobbit = ?', "oui")
		->where('est_en_hibernation_hobbit = ?', "non")
		->where('est_compte_desactive_hobbit = ?', "non")
		->where('est_pnj_hobbit = ?', "non")
		->where('date_fin_tour_hobbit <= ?',$dateFin);
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findAllJoueurs() {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('hobbit', '*')
		->where('est_compte_actif_hobbit = ?', "oui")
		->where('est_pnj_hobbit = ?', "non")
		->where('est_compte_desactive_hobbit = ?', "non");
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findAllJoueursAvecPnj() {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('hobbit', '*')
		->where('est_compte_actif_hobbit = ?', "oui")
		->order('id_hobbit');

		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findAllCompteInactif($dateFin) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('hobbit', '*')
		->where('est_compte_actif_hobbit = ?', "non")
		->where('est_pnj_hobbit = ?', "non")
		->where('est_compte_desactive_hobbit = ?', "non")
		->where('date_creation_hobbit <= ?',$dateFin);
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function deleteAllBatchByDateFin($dateFin) {
		$where = "est_compte_actif_hobbit = 'oui' AND est_pnj_hobbit = 'non' AND est_en_hibernation_hobbit = 'non' AND est_compte_desactive_hobbit = 'non' AND date_fin_tour_hobbit <= '".$dateFin."'";
		return $this->delete($where);
	}

	function deleteAllCompteInactif($dateFin) {
		$db = $this->getAdapter();
		$where = "est_compte_actif_hobbit = 'non' AND est_pnj_hobbit = 'non' AND est_compte_desactive_hobbit = 'non' AND date_creation_hobbit <= '".$dateFin."'";
		return $this->delete($where);
	}


	function findAllJoueursWithGardeEmail() {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('hobbit', '*')
		->where('est_compte_actif_hobbit = ?', "oui")
		->where('est_pnj_hobbit = ?', "non")
		->where('est_compte_desactive_hobbit = ?', "non")
		->where('beta_conserver_nom_hobbit = ?', "oui");
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}

