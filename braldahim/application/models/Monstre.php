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

	function countAllByType($id_type) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('monstre', 'count(id_monstre) as nombre')
		->where('id_fk_type_monstre = ?', intval($id_type));
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		$nombre = $resultat[0]["nombre"];
		return $nombre;
	}

	function countAllByTaille($id_taille) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('monstre', 'count(id_monstre) as nombre')
		->where('id_fk_taille_monstre = ?', intval($id_taille));
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		$nombre = $resultat[0]["nombre"];
		return $nombre;
	}

	function countVue($x_min, $y_min, $x_max, $y_max) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('monstre', 'count(id_monstre) as nombre')
		->where('x_monstre <= ?',$x_max)
		->where('x_monstre >= ?',$x_min)
		->where('y_monstre >= ?',$y_min)
		->where('y_monstre <= ?',$y_max)
		->where('est_mort_monstre = ?', 'non');
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);
		$nombre = $resultat[0]["nombre"];
		return $nombre;
	}

	function selectVue($x_min, $y_min, $x_max, $y_max) {
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
		->where('est_mort_monstre = ?', "non");
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
	function findByCase($x, $y) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('monstre', '*')
		->from('type_monstre', '*')
		->from('taille_monstre', '*')
		->where('monstre.id_fk_type_monstre = type_monstre.id_type_monstre')
		->where('monstre.id_fk_taille_monstre = taille_monstre.id_taille_monstre')
		->where('x_monstre = ?',$x)
		->where('y_monstre = ?',$y);
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

	function findByGroupeId($idGroupe) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('monstre', '*')
		->from('type_monstre', '*')
		->from('taille_monstre', '*')
		->where('monstre.id_fk_type_monstre = type_monstre.id_type_monstre')
		->where('monstre.id_fk_taille_monstre = taille_monstre.id_taille_monstre')
		->where('monstre.id_fk_groupe_monstre = ?', intval($idGroupe))
		->where('est_mort_monstre = ?', "non");
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}
