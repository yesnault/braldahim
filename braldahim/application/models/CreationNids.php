<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class CreationNids extends Zend_Db_Table {
	protected $_name = 'creation_nid';
	protected $_primary = array('id_fk_zone_creation_nid', 'id_fk_type_monstre_creation_nid');

	public function findByIdZoneNid($idZone) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('creation_nid', '*')
		->where('id_fk_zone_creation_nid = ?', $idZone);
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		return $resultat;
	}

	public function findIdTypeMonstreNiveauMinMaxByIdZone($idZone, $niveauMin, $niveauMax) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('creation_nid', null)
		->from('type_monstre', 'id_type_monstre')
		->from('ref_monstre', null)
		->where('id_fk_zone_creation_nid = ?', $idZone)
		->where('id_type_monstre = id_fk_type_monstre_creation_nid')
		->where('id_type_monstre = id_fk_type_ref_monstre')
		->where('niveau_min_ref_monstre >= ?', $niveauMin)
		->where('niveau_max_ref_monstre <= ?', $niveauMax)
		->group('id_type_monstre');
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		return $resultat;
	}
}