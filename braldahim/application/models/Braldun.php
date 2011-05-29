<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Braldun extends Zend_Db_Table {
	protected $_name = 'braldun';
	protected $_primary = 'id_braldun';

	protected $_dependentTables = array('bralduns_competences', 'gardiennage');

	function findAll($page, $nbMax) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('braldun', '*');
		$select->order(array('nom_braldun', 'prenom_braldun'));
		$select->limitPage($page, $nbMax);
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findByCriteres($niveau = -1 , $page = null, $nbMax = null, $ordre = null, $sens = null, $where = null) {
		if ($niveau != -1) {
			$and = " niveau_braldun = ".intval($niveau);
		} else {
			$and = null;
		}

		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('braldun')
		->where('est_compte_actif_braldun = ?', "oui")
		->where('est_en_hibernation_braldun = ?', "non");

		if ($and != null) {
			$select->where($and);
		}

		if ($ordre != null && $sens != null) {
			$select->order($ordre.$sens);
		} else {
			$select->order("prenom_braldun");
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
		$select->from('braldun', 'distinct(niveau_braldun) as niveau_braldun');
		$select->order('niveau_braldun');
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function selectVue($x_min, $y_min, $x_max, $y_max, $z, $sansBraldunCourant = -1, $avecIntangibles = true, $avecKo = false) {
		$db = $this->getAdapter();
		$select = $db->select();

		$select->from('braldun', '*');
		$select->where('x_braldun <= ?', $x_max);
		$select->where('x_braldun >= ?', $x_min);
		$select->where('y_braldun >= ?', $y_min);
		$select->where('y_braldun <= ?', $y_max);
		$select->where('z_braldun = ?', $z);
		$select->where('est_compte_actif_braldun = ?', "oui");
		$select->where('est_en_hibernation_braldun = ?', "non");
		$select->where('est_pnj_braldun = ?', "non");

		if ($avecIntangibles == false) {
			$select->where("est_intangible_braldun like ?", "non");
		}
			
		if ($sansBraldunCourant != -1) {
			$select->where('id_braldun != ?', $sansBraldunCourant);
		}

		if ($avecKo == false) {
			$select->where('est_ko_braldun = ?', "non");
		}

		$select->joinLeft('communaute','id_fk_communaute_braldun = id_communaute');

		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findByCase($x, $y, $z, $sansBraldunCourant = -1, $avecIntangibles = true, $braldunKoSeulement = false) {
		$db = $this->getAdapter();
		$select = $db->select();

		$select->from('braldun', '*');
		$select->where('x_braldun = ?',$x);
		$select->where('y_braldun = ?',$y);
		$select->where('z_braldun = ?',$z);
		$select->where('est_compte_actif_braldun = ?', "oui");
		$select->where('est_en_hibernation_braldun = ?', "non");
		if ($braldunKoSeulement) {
			$select->where('est_ko_braldun = ?', "oui");
		} else {
			$select->where('est_ko_braldun = ?', "non");
		}
		$select->where('est_pnj_braldun = ?', "non");

		if ($sansBraldunCourant != -1) {
			$select->where('id_braldun != ?',$sansBraldunCourant);
		}

		if ($avecIntangibles == false) {
			$select->where("est_intangible_braldun like ?", "non");
		}

		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	public function findById($id){
		$where = $this->getAdapter()->quoteInto('id_braldun = ?',(int)$id);
		return $this->fetchRow($where);
	}

	function findNomById($id) {
		$where = $this->getAdapter()->quoteInto('id_braldun = ?',(int)$id);
		$braldun = $this->fetchRow($where);

		if ($braldun == null) {
			$retour = "braldun inconnu";
		} else {
			$retour = $braldun["prenom_braldun"]. " ".$braldun["nom_braldun"]. " (".$braldun["id_braldun"].")";
		}
		return $retour;
	}

	public function findByIdList($listId){
		return $this->findByList("id_braldun", $listId);
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
			$select->from('braldun', '*')
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
		$select->from('braldun', '*')
		->where('id_fk_nom_initial_braldun = ?', $idNom)
		->where('lcase(prenom_braldun) like ?', (string)mb_strtolower(trim($prenom)));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	public function findByEmail($email){
		$where = $this->getAdapter()->quoteInto('lcase(email_braldun) = ?',(string)mb_strtolower(trim($email)));
		return $this->fetchRow($where);
	}

	function findLesPlusProches($x, $y, $z, $rayon, $nombre, $idTypeMonstre = null, $avecIntangibles = true) {
		$and = "";
		if ($idTypeMonstre != null) {
			$and = " AND id_fk_type_monstre_effet_mot_f != ".(int)$idTypeMonstre;
		}

		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('braldun', '*, SQRT(((x_braldun - '.$x.') * (x_braldun - '.$x.')) + ((y_braldun - '.$y.') * ( y_braldun - '.$y.'))) as distance')
		->where('x_braldun >= ?', $x - $rayon)
		->where('x_braldun <= ?', $x + $rayon)
		->where('y_braldun >= ?', $y - $rayon)
		->where('y_braldun <= ?', $y + $rayon)
		->where('z_braldun = ?', $z)
		->where("est_ko_braldun = 'non'")
		->where('est_compte_actif_braldun = ?', "oui")
		->where('est_en_hibernation_braldun = ?', "non")
		->where('est_pnj_braldun = ?', "non");

		if ($avecIntangibles == false) {
			$select->where("est_intangible_braldun like ?", "non");
		}

		$select->joinLeft('effet_mot_f','id_fk_braldun_effet_mot_f = id_braldun')
		->limit($nombre)
		->order(array('distance ASC','niveau_braldun ASC'));

		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findBraldunAvecRayon($x, $y, $z, $rayon, $idBraldun, $avecIntangibles) {

		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('braldun', '*')
		->where('x_braldun >= ?', $x - $rayon)
		->where('x_braldun <= ?', $x + $rayon)
		->where('y_braldun >= ?', $y - $rayon)
		->where('y_braldun <= ?', $y + $rayon)
		->where('z_braldun = ?', $z)
		->where('est_ko_braldun = ?', "non")
		->where('est_compte_actif_braldun = ?', "oui")
		->where('est_en_hibernation_braldun = ?' ,"non")
		->where('est_pnj_braldun = ?', "non");

		if ($idBraldun != null) {
			$select->where('id_braldun = ?', $idBraldun);
		}

		if ($avecIntangibles == false) {
			$select->where("est_intangible_braldun like ?", "non");
		}

		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
	function getSaltByEmail($email) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('braldun', 'password_salt_braldun')
		->where('lcase(email_braldun) = ?',(string)mb_strtolower(trim($email)));
		$sql = $select->__toString();
		return $db->fetchrow($sql);
	}

	function findBraldunsParNomPrenom($nom, $prenom) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('braldun', '*')
		->where('lcase(nom_braldun) like ?', (string)mb_strtolower(trim($nom)))
		->where('lcase(prenom_braldun) like ?', (string)mb_strtolower(trim($prenom)));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findBraldunsParPrenom($prenom, $sansIdBraldun = null, $avecPnj = null) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('braldun', '*')
		->where('lcase(prenom_braldun) like ?', (string)mb_strtolower(trim($prenom)));

		if ($sansIdBraldun != null) {
			$select->where('id_braldun != ?', intval($sansIdBraldun));
		}

		if ($avecPnj != null) {
			if ($avecPnj === true) {
				$select->where("est_pnj_braldun='oui'");
			} else {
				$select->where("est_pnj_braldun='non'");
			}
		}

		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findBraldunsParPrenomAndIdTypeDistinction($prenom, $idTypeDistinction) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('braldun', '*')
		->from('bralduns_distinction', null)
		->where('id_fk_type_distinction_hdistinction = ?', intval($idTypeDistinction))
		->where('id_fk_braldun_hdistinction = id_braldun')
		->where('lcase(prenom_braldun) like ?', (string)mb_strtolower(trim($prenom)));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findByIdListAndIdTypeDistinction($listId, $idTypeDistinction) {

		$liste = "";
		$nomChamp = "id_braldun";
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
			$select->from('braldun', '*')
			->from('bralduns_distinction', null)
			->where('id_fk_type_distinction_hdistinction = ?', intval($idTypeDistinction))
			->where('id_fk_braldun_hdistinction = id_braldun')
			->where($nomChamp .'='. $liste);
			$sql = $select->__toString();
			return $db->fetchAll($sql);
		} else {
			return null;
		}
	}

	function findBraldunsMasculinSansConjoint($idBraldun) {
		$db = $this->getAdapter();
		$sql = "SELECT id_braldun, nom_braldun, prenom_braldun, niveau_braldun FROM braldun WHERE sexe_braldun='masculin' AND est_compte_actif_braldun='oui' AND niveau_braldun > 5 AND est_pnj_braldun='non' AND id_braldun <> ".(int)$idBraldun." AND id_braldun NOT IN (SELECT id_fk_m_braldun_couple FROM couple)";
		return $db->fetchAll($sql);
	}

	function findBraldunsFemininSansConjoint($idBraldun) {
		$db = $this->getAdapter();
		$sql = "SELECT id_braldun, nom_braldun, prenom_braldun, niveau_braldun FROM braldun WHERE sexe_braldun='feminin' AND est_compte_actif_braldun='oui' AND niveau_braldun > 5 AND est_pnj_braldun='non' AND id_braldun <> ".(int)$idBraldun." AND id_braldun NOT IN (SELECT id_fk_f_braldun_couple FROM couple)";
		return $db->fetchAll($sql);
	}

	function findEnfants($sexe, $idBraldun) {
		$db = $this->getAdapter();
		$select = $db->select();
		if ($sexe == "masculin") {
			$select->from('braldun', '*')
			->where('id_fk_pere_braldun = ?', (int)$idBraldun);
		} else {
			$select->from('braldun', '*')
			->where('id_fk_mere_braldun = ?', (int)$idBraldun);
		}
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	public function findByIdCommunaute($idCommunaute, $idRang = -1 , $page = null, $nbMax = null, $ordre = null, $sens = null) {
		if ($idRang != -1) {
			$and = " AND id_fk_rang_communaute_braldun = ".intval($idRang);
		} else {
			$and = "";
		}

		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('braldun')
		->from('communaute')
		->from('rang_communaute')
		->where('id_fk_communaute_braldun = ?', intval($idCommunaute))
		->where('id_fk_rang_communaute_braldun = id_rang_communaute')
		->where('id_rang_communaute = id_fk_rang_communaute_braldun')
		->where("id_communaute = id_fk_communaute_braldun".$and);
		
		if ($ordre != null && $sens != null) {
			$select->order($ordre.$sens);
		} else {
			$select->order("prenom_braldun");
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
		$select->from('braldun', 'count(*) as nombre')
		->where('id_fk_communaute_braldun = ?', intval($idCommunaute));
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		$nombre = $resultat[0]["nombre"];
		return $nombre;
	}

	function findAllBatchByDateFin($dateFin, $inverse = false) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('braldun', '*')
		->where('est_compte_actif_braldun = ?', "oui")
		->where('est_en_hibernation_braldun = ?', "non")
		->where('est_compte_desactive_braldun = ?', "non")
		->where('est_pnj_braldun = ?', "non");
		if ($inverse == false) {
			$select->where('date_fin_tour_braldun <= ?',$dateFin);
		} else {
			$select->where('date_fin_tour_braldun >= ?',$dateFin);
		}
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function countAllBatchByDateFin($dateFin, $inverse = false) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('braldun', 'count(*) as nombre')
		->where('est_compte_actif_braldun = ?', "oui")
		->where('est_en_hibernation_braldun = ?', "non")
		->where('est_compte_desactive_braldun = ?', "non")
		->where('est_pnj_braldun = ?', "non");
		if ($inverse == false) {
			$select->where('date_fin_tour_braldun <= ?',$dateFin);
		} else {
			$select->where('date_fin_tour_braldun >= ?',$dateFin);
		}
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);
		$nombre = $resultat[0]["nombre"];
		return $nombre;
	}

	function findAllJoueurs() {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('braldun', '*')
		->where('est_compte_actif_braldun = ?', "oui")
		->where('est_pnj_braldun = ?', "non")
		->where('est_compte_desactive_braldun = ?', "non");
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findAllGredins($niveauMin, $niveauMax) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('braldun', '*')
		->where('est_compte_actif_braldun = ?', "oui")
		->where('est_pnj_braldun = ?', "non")
		->where('est_compte_desactive_braldun = ?', "non")
		->where('points_gredin_braldun > 0')
		->where('niveau_braldun >= ?', $niveauMin)
		->where('niveau_braldun <= ?', $niveauMax);
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findAllRedresseurs($niveauMin, $niveauMax) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('braldun', '*')
		->where('est_compte_actif_braldun = ?', "oui")
		->where('est_pnj_braldun = ?', "non")
		->where('est_compte_desactive_braldun = ?', "non")
		->where('points_redresseur_braldun > 0')
		->where('niveau_braldun >= ?', $niveauMin)
		->where('niveau_braldun <= ?', $niveauMax);
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findAllJoueursAvecPnj() {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('braldun', '*')
		->where('est_compte_actif_braldun = ?', "oui")
		->order('id_braldun');

		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findAllCompteInactif($dateFin = null) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('braldun', '*')
		->where('est_compte_actif_braldun = ?', "non")
		->where('est_pnj_braldun = ?', "non")
		->where('est_compte_desactive_braldun = ?', "non");
		if ($dateFin != null) {
			$select->where('date_creation_braldun <= ?',$dateFin); // tous les plus vieux
		}
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findAllCompteDesactives() {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('braldun', '*')
		->where('est_compte_desactive_braldun = ?', "oui");
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function countAllCompteActifInactif($dateFin, $estActif) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('braldun', 'count(*) as nombre')
		->where('est_pnj_braldun = ?', "non")
		->where('est_compte_desactive_braldun = ?', "non")
		->where('date_creation_braldun >= ?',$dateFin); // tous les plus jeunes
		if ($estActif) {
			$select->where('est_compte_actif_braldun = ?', "oui");
		} else {
			$select->where('est_compte_actif_braldun = ?', "non");
		}
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);
		$nombre = $resultat[0]["nombre"];
		return $nombre;
	}

	function countAllCompteActif($dateFin = null) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('braldun', 'count(*) as nombre')
		->where('est_pnj_braldun = ?', "non")
		->where('est_compte_desactive_braldun = ?', "non")
		->where('est_compte_actif_braldun = ?', "oui")
		->where('est_en_hibernation_braldun = ?', "non");

		if ($dateFin != null) {
			$select->where('date_fin_tour_braldun >= ?', $dateFin);
		}

		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);
		$nombre = $resultat[0]["nombre"];
		return $nombre;
	}

	function countAllHibernation($dateFin = null) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('braldun', 'count(*) as nombre')
		->where('est_en_hibernation_braldun = ?', "oui");
		if ($dateFin != null) {
			$select->where('date_fin_hibernation_braldun >= ?', $dateFin);
		}
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);
		$nombre = $resultat[0]["nombre"];
		return $nombre;
	}

	function deleteAllBatchByDateFin($dateFin) {
		$where = "est_compte_actif_braldun = 'oui' AND est_pnj_braldun = 'non' AND est_en_hibernation_braldun = 'non' AND est_compte_desactive_braldun = 'non' AND date_fin_tour_braldun <= '".$dateFin."'";
		return $this->delete($where);
	}

	function deleteAllCompteInactif($dateFin) {
		$db = $this->getAdapter();
		$where = "est_compte_actif_braldun = 'non' AND est_pnj_braldun = 'non' AND est_compte_desactive_braldun = 'non' AND date_creation_braldun <= '".$dateFin."'";
		return $this->delete($where);
	}
}

