<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Monstre extends Zend_Db_Table {
	protected $_name = 'monstre';
	protected $_primary = "id_monstre";

	function countAll() {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('monstre', 'count(id_monstre) as nombre')
		->where('est_mort_monstre = ?', 'non');
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		$nombre = $resultat[0]["nombre"];
		return $nombre;
	}

	function countAllByType($idType) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('monstre', 'count(id_monstre) as nombre')
		->where('id_fk_type_monstre = ?', intval($idType))
		->where('est_mort_monstre = ?', 'non');
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		$nombre = $resultat[0]["nombre"];
		return $nombre;
	}

	function countAllByIdZoneNid($idZone) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('monstre', 'count(id_monstre) as nombre')
		->where('id_fk_zone_nid_monstre = ?', intval($idZone))
		->where('est_mort_monstre = ?', 'non');
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		$nombre = $resultat[0]["nombre"];
		return $nombre;
	}

	function countAllByTypeAndIdZoneNid($idZone = null) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('monstre', array('count(id_monstre) as nombre', 'id_fk_type_monstre', 'id_fk_zone_nid_monstre', 'sum(niveau_monstre) as totalNiveau'));
		if ($idZone != null) {
			$select->where('id_fk_zone_nid_monstre = ?', intval($idZone));
		}
		$select->where('est_mort_monstre = ?', 'non');
		$select->group(array('id_fk_type_monstre', 'id_fk_zone_nid_monstre'));
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		return $resultat;
	}

	function findVivantByIdZoneNidAndIdType($idZone, $idTypeMonstre) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('monstre', array('id_monstre', 'id_fk_type_monstre', 'niveau_monstre'))
		->where('id_fk_zone_nid_monstre = ?', intval($idZone))
		->where('est_mort_monstre = ?', 'non')
		->where('id_fk_type_monstre = ?', intval($idTypeMonstre))
		->order('date_creation_monstre desc');
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		return $resultat;
	}

	function countAllByTaille($id_taille) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('monstre', 'count(id_monstre) as nombre')
		->where('id_fk_taille_monstre = ?', intval($id_taille))
		->where('est_mort_monstre = ?', 'non');
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		$nombre = $resultat[0]["nombre"];
		return $nombre;
	}

	function countVue($x_min, $y_min, $x_max, $y_max, $z, $id_type = null) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('monstre', 'count(id_monstre) as nombre')
		->where('x_monstre <= ?',$x_max)
		->where('x_monstre >= ?',$x_min)
		->where('y_monstre >= ?',$y_min)
		->where('y_monstre <= ?',$y_max)
		->where('z_monstre = ?',$z)
		->where('est_mort_monstre = ?', 'non');

		if ($id_type != null) {
			$select->where('id_fk_type_monstre = ?',$id_type);
		}

		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);
		$nombre = $resultat[0]["nombre"];
		return $nombre;
	}

	function selectVue($x_min, $y_min, $x_max, $y_max, $z) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('monstre', '*')
		->from('type_monstre', '*')
		->from('taille_monstre', '*')
		->where('monstre.id_fk_type_monstre = type_monstre.id_type_monstre')
		->where('monstre.id_fk_taille_monstre = taille_monstre.id_taille_monstre')
		->where('x_monstre <= ?',$x_max)
		->where('x_monstre >= ?',$x_min)
		->where('y_monstre >= ?',$y_min)
		->where('y_monstre <= ?',$y_max)
		->where('z_monstre = ?',$z)
		->where('est_mort_monstre = ?', "non");
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function selectVueCadavre($x_min, $y_min, $x_max, $y_max, $z) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('monstre', '*')
		->from('type_monstre', '*')
		->from('taille_monstre', '*')
		->where('monstre.id_fk_type_monstre = type_monstre.id_type_monstre')
		->where('monstre.id_fk_taille_monstre = taille_monstre.id_taille_monstre')
		->where('x_monstre <= ?',$x_max)
		->where('x_monstre >= ?',$x_min)
		->where('y_monstre >= ?',$y_min)
		->where('y_monstre <= ?',$y_max)
		->where('z_monstre = ?',$z)
		->where('est_mort_monstre = ?','oui')
		->where('est_depiaute_cadavre = ?', 'non');
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findByCaseCadavre($x, $y, $z) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('monstre', '*')
		->from('type_monstre', '*')
		->from('taille_monstre', '*')
		->where('monstre.id_fk_type_monstre = type_monstre.id_type_monstre')
		->where('monstre.id_fk_taille_monstre = taille_monstre.id_taille_monstre')
		->where('x_monstre = ?',$x)
		->where('y_monstre = ?',$y)
		->where('z_monstre = ?',$z)
		->where('est_mort_monstre = ?','oui')
		->where('est_depiaute_cadavre = ?', 'non');
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findByCase($x, $y, $z, $idGroupeAExclure = null) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('monstre', '*')
		->from('type_monstre', '*')
		->from('taille_monstre', '*')
		->where('monstre.id_fk_type_monstre = type_monstre.id_type_monstre')
		->where('monstre.id_fk_taille_monstre = taille_monstre.id_taille_monstre')
		->where('x_monstre = ?',$x)
		->where('y_monstre = ?',$y)
		->where('z_monstre = ?',$z)
		->where('est_mort_monstre = ?', 'non');
		
		if ($idGroupeAExclure != null) {
			$select->where('id_fk_groupe_monstre != ?', $idGroupeAExclure);
		}
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findById($id) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('monstre', '*')
		->from('type_monstre', '*')
		->from('taille_monstre', '*')
		->where('monstre.id_fk_type_monstre = type_monstre.id_type_monstre')
		->where('monstre.id_fk_taille_monstre = taille_monstre.id_taille_monstre')
		->where('id_monstre = ?',$id);
		$sql = $select->__toString();
		return $db->fetchRow($sql);
	}

	function findNomById($id) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('monstre', '*')
		->from('type_monstre', '*')
		->from('taille_monstre', '*')
		->where('monstre.id_fk_type_monstre = type_monstre.id_type_monstre')
		->where('monstre.id_fk_taille_monstre = taille_monstre.id_taille_monstre')
		->where('id_monstre = ?',$id);
		$sql = $select->__toString();
		$monstre = $db->fetchRow($sql);
		if ($monstre == null) {
			$retour = "monstre inconnu";
		} else {
			$retour = $monstre["nom_type_monstre"]. " (".$monstre["id_monstre"].")";
		}
		return $retour;
	}

	function findByGroupeId($idGroupe) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('monstre', '*')
		->from('type_monstre', '*')
		->from('taille_monstre', '*')
		->from('groupe_monstre', '*')
		->where('monstre.id_fk_type_monstre = type_monstre.id_type_monstre')
		->where('monstre.id_fk_taille_monstre = taille_monstre.id_taille_monstre')
		->where('monstre.id_fk_groupe_monstre = id_groupe_monstre')
		->where('monstre.id_fk_groupe_monstre = ?', intval($idGroupe))
		->where('est_mort_monstre = ?', "non");

		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findLePlusProcheParType($idtype, $x, $y, $z, $rayon) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('monstre', 'id_monstre, y_monstre, x_monstre, id_fk_type_monstre, SQRT(((x_monstre - '.$x.') * (x_monstre - '.$x.')) + ((y_monstre - '.$y.') * ( y_monstre - '.$y.'))) as distance')
		->from('type_monstre', '*')
		->where('x_monstre >= ?', $x - $rayon)
		->where('x_monstre <= ?', $x + $rayon)
		->where('y_monstre >= ?', $y - $rayon)
		->where('y_monstre <= ?', $y + $rayon)
		->where('z_monstre = ?', $z)
		->where('monstre.id_fk_type_monstre = type_monstre.id_type_monstre')
		->where('type_monstre.id_type_monstre = ?', intval($idtype))
		->where('est_mort_monstre = ?', "non")
		->order('distance ASC');
		$sql = $select->__toString();
		return $db->fetchRow($sql);
	}

	/**
	 * Supprime les monstres qui sont en ville.
	 */
	function deleteInVille() {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('ville', '*');

		$sql = $select->__toString();
		$villes = $db->fetchAll($sql);

		foreach($villes as $v) {
			$where = " x_monstre >= ". $v["x_min_ville"];
			$where .= " AND x_monstre <= ". $v["x_max_ville"];
			$where .= " AND y_monstre >= ". $v["y_min_ville"];
			$where .= " AND y_monstre <= ". $v["y_max_ville"];
			$this->delete($where);
		}
	}

	function findMonstresAJouerSansGroupe($aJouerFlag, $nombreMax, $estGibier) {

		$config = Zend_Registry::get('config');

		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('monstre', '*')
		->from('type_monstre', '*')
		->from('taille_monstre', '*')
		->where('monstre.id_fk_type_monstre = type_monstre.id_type_monstre')
		->where('monstre.id_fk_taille_monstre = taille_monstre.id_taille_monstre')
		->where('est_mort_monstre = ?', 'non');

		if ($estGibier) {
			$select->where('type_monstre.id_fk_type_groupe_monstre = ?', (int)$config->game->groupe_monstre->type->gibier);
		} else {
			$select->where('type_monstre.id_fk_type_groupe_monstre != ?', (int)$config->game->groupe_monstre->type->gibier);
		}

		if ($aJouerFlag != "") {
			$select->where('date_a_jouer_monstre <= ?', date("Y-m-d H:i:s"));
		}
		$select->where('id_fk_groupe_monstre is NULL');
		$select->order('date_fin_tour_monstre ASC');
		$select->limitPage(0, $nombreMax);
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findByIdZoneNidMinAndIdZoneNidMax($idZoneMin, $idZoneMax) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('monstre', array('x_monstre', 'y_monstre', 'niveau_monstre'))
		->where('id_fk_zone_nid_monstre >= ?', intval($idZoneMin))
		->where('id_fk_zone_nid_monstre <= ?', intval($idZoneMax))
		->where('est_mort_monstre = ?', 'non');
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);
		return $resultat;
	}

	function findSolitaireDirectionHorsZone() {
		return $this->findMonstreDirectionHorsZone("solitaire");
	}

	function findNueeDirectionHorsZone() {
		return $this->findMonstreDirectionHorsZone("groupe", 20);
	}

	function findMonstreDirectionHorsZone($type, $tolerance = 0) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('monstre', '*');

		$where = 'x_direction_monstre > x_max_monstre + '.$tolerance.'  OR ';
		$where .= 'x_direction_monstre < x_min_monstre - '.$tolerance.'  OR ';
		$where .= 'y_direction_monstre > y_max_monstre + '.$tolerance.'  OR ';
		$where .= 'y_direction_monstre < y_min_monstre - '.$tolerance.' ';

		$select->where($where);
		$select->where('est_mort_monstre like ?', 'non');
		if ($type == "groupe") {
			$select->where('id_fk_groupe_monstre is not null');
		} else {
			$select->where('id_fk_groupe_monstre is null');
		}
		$select->where('nb_dla_jouees_monstre > 0');
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);
		return $resultat;
	}

	function findSolitairePositionHorsZone() {
		return $this->findMonstreDirectionHorsZone("solitaire");
	}

	function findNueePositionHorsZone() {
		return $this->findMonstrePositionHorsZone("groupe", 20);
	}

	function findMonstrePositionHorsZone($type, $tolerance = 0) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('monstre', '*');

		$where = 'x_monstre > x_max_monstre + '.$tolerance.' OR ';
		$where .= 'x_monstre < x_min_monstre - '.$tolerance.'  OR ';
		$where .= 'y_monstre > y_max_monstre + '.$tolerance.'  OR ';
		$where .= 'y_monstre < y_min_monstre - '.$tolerance.' ';

		$select->where($where);
		$select->where('est_mort_monstre like ?', 'non');
		if ($type == "groupe") {
			$select->where('id_fk_groupe_monstre is not null');
		} else {
			$select->where('id_fk_groupe_monstre is null');
		}
		$select->where('nb_dla_jouees_monstre > 0');
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);
		return $resultat;
	}

}