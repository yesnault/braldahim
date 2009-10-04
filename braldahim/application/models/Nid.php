<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: $
 * $Author: $
 * $LastChangedDate: $
 * $LastChangedRevision: $
 * $LastChangedBy: $
 */
class Nid extends Zend_Db_Table {
	protected $_name = 'nid';
	protected $_primary = 'id_nid';

	function countMonstresACreerByIdZone($idZone) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('nid', 'sum(nb_monstres_restants_nid) as nombre')
		->where('id_fk_zone_nid = ?', $idZone);
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		$nombre = $resultat[0]["nombre"];
		if ($nombre == null) {
			$nombre = 0;
		}
		return $nombre;
	}

	function countMonstresACreerByTypeMonstreAndIdZone($idZone) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('nid', array('sum(nb_monstres_restants_nid) as nombre', 'id_fk_type_monstre_nid'))
		->where('id_fk_zone_nid = ?', $idZone)
		->group('id_fk_type_monstre_nid');
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		return $resultat;
	}

	function selectVue($x_min, $y_min, $x_max, $y_max, $z) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('nid', '*')
		->from('type_monstre', '*')
		->where('id_fk_type_monstre_nid = id_type_monstre')
		->where('x_nid <= ?',$x_max)
		->where('x_nid >= ?',$x_min)
		->where('y_nid >= ?',$y_min)
		->where('y_nid <= ?',$y_max)
		->where('z_nid = ?',$z);
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findACreerByTypeMonstreAndIdZone($idZone, $idTypeMonstre) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('nid', array('id_nid', 'id_fk_type_monstre_nid', 'nb_monstres_restants_nid'))
		->where('id_fk_zone_nid = ?', $idZone)
		->where('id_fk_type_monstre_nid = ?', $idTypeMonstre);
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		return $resultat;
	}

	function findByIdZoneNid($idZone) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('nid', '*')
		->where('id_fk_zone_nid = ?', $idZone);
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		return $resultat;
	}
}