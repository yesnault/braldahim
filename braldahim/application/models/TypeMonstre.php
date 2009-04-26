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
class TypeMonstre extends Zend_Db_Table {
	protected $_name = 'type_monstre';
	protected $_primary = "id_type_monstre";

	public function fetchAllAvecTypeGroupe() {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('type_monstre', '*')
		->from('type_groupe_monstre', '*')
		->where('type_monstre.id_fk_type_groupe_monstre = type_groupe_monstre.id_type_groupe_monstre');
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}

	public function fetchAllByTypeGroupe($typeGroupeMonstre) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('type_monstre', '*')
		->where('type_monstre.id_fk_type_groupe_monstre = ?', (int)$typeGroupeMonstre);
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}


}