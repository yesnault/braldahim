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
class Materiel extends Zend_Db_Table {
	protected $_name = 'materiel';
	protected $_primary = array('id_materiel');

	function findByIdMaterielWithDetails($idMateriel) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('materiel', '*')
		->from('type_materiel')
		->where('id_fk_type_materiel = id_type_materiel')
		->where('id_materiel = ?', intval($idMateriel))
		->order(array("nom_type_materiel"));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findNomById($id) {
		$materiels = $this->findByIdMaterielWithDetails($id);

		if ($materiels == null || count($materiels) != 1) {
			$retour = "materiel inconnu";
		} else {
			$materiel = $materiels[0];
			$retour = $materiel["nom_type_materiel"]. " (".$materiel["id_materiel"].")";
		}
		return $retour;
	}
}
