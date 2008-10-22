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
class Cadavre extends Zend_Db_Table {
	protected $_name = 'cadavre';
	protected $_primary = "id_cadavre";

	function countAll() {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('cadavre', 'count(id_cadavre) as nombre');
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		$nombre = $resultat[0]["nombre"];
		return $nombre;
	}

	function countVue($x_min, $y_min, $x_max, $y_max) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('cadavre', 'count(id_cadavre) as nombre')
		->where('x_cadavre <= ?',$x_max)
		->where('x_cadavre >= ?',$x_min)
		->where('y_cadavre >= ?',$y_min)
		->where('y_cadavre <= ?',$y_max);
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);
		$nombre = $resultat[0]["nombre"];
		return $nombre;
	}

	function selectVue($x_min, $y_min, $x_max, $y_max) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('cadavre', '*')
		->from('type_monstre', '*')
		->from('taille_monstre', '*')
		->where('cadavre.id_fk_type_monstre_cadavre = type_monstre.id_type_monstre')
		->where('cadavre.id_fk_taille_cadavre = taille_monstre.id_taille_monstre')
		->where('x_cadavre <= ?',$x_max)
		->where('x_cadavre >= ?',$x_min)
		->where('y_cadavre >= ?',$y_min)
		->where('y_cadavre <= ?',$y_max);
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
	function findByCase($x, $y) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('cadavre', '*')
		->from('type_monstre', '*')
		->from('taille_monstre', '*')
		->where('cadavre.id_fk_type_monstre_cadavre = type_monstre.id_type_monstre')
		->where('cadavre.id_fk_taille_cadavre = taille_monstre.id_taille_monstre')
		->where('x_cadavre = ?',$x)
		->where('y_cadavre = ?',$y);
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
	function findById($id) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('cadavre', '*')
		->from('type_monstre', '*')
		->from('taille_monstre', '*')
		->where('cadavre.id_fk_type_monstre_cadavre = type_monstre.id_type_monstre')
		->where('cadavre.id_fk_taille_cadavre = taille_monstre.id_taille_monstre')
		->where('id_cadavre = ?', $id);
		$sql = $select->__toString();
		return $db->fetchRow($sql);
	}
}
