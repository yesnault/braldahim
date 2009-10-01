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
		$select->from('nid', 'count(nb_monstres_restants_nid) as nombre')
		->where('id_fk_zone_nid = ?', $idZone);
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		$nombre = $resultat[0]["nombre"];
		return $nombre;
	}

	function countMonstresACreerByTypeMonstreAndIdZone($idZone) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('nid', array('count(nb_monstres_restants_nid) as nombre', 'id_fk_type_monstre_nid'))
		->where('id_fk_zone_nid = ?', $idZone)
		->group('id_fk_type_monstre_nid');
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		return $resultat;
	}
}