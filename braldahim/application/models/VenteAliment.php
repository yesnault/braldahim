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
class VenteAliment extends Zend_Db_Table {
	protected $_name = 'vente_aliment';
	protected $_primary = array('id_vente_aliment');

	function findByIdVente($idVente) {
		$db = $this->getAdapter();
		$select->from('vente_aliment', '*')
		->from('type_aliment')
		->from('type_qualite')
		->where('id_fk_type_vente_aliment = id_type_aliment')
		->where('id_fk_type_qualite_vente_aliment = id_type_qualite')
		->where('id_fk_vente_aliment = '.intval($idVente));

		return $db->fetchAll($sql);
	}
}