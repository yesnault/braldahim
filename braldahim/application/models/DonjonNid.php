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
class DonjonNid extends Zend_Db_Table {
	protected $_name = 'donjon_nid';
	protected $_primary = "id_donjon_nid";

	function findByIdDonjonCreation($idDonjon) {
		return $this->findByIdDonjon($idDonjon, "creation");
	}

	function findByIdDonjonEchec($idDonjon) {
		return $this->findByIdDonjon($idDonjon, "echec");
	}

	function findByIdDonjon($idDonjon, $type) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('donjon_nid', '*')
		->from('type_monstre', '*')
		->from('type_groupe_monstre', '*')
		->where('id_fk_type_monstre_donjon_nid = type_monstre.id_type_monstre')
		->where('type_monstre.id_fk_type_groupe_monstre = type_groupe_monstre.id_type_groupe_monstre')
		->where('id_fk_donjon_nid = ?', intval($idDonjon))
		->where('type_donjon_nid = ?', $type);
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}
