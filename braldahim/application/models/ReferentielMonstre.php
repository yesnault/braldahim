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
class ReferentielMonstre extends Zend_Db_Table {
	protected $_name = 'ref_monstre';
	protected $_primary = "id_ref_monstre";

	public function findAll() {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('ref_monstre', '*')
		->from('type_monstre', '*')
		->from('taille_monstre', '*')
		->from('type_groupe_monstre', '*')
		->where('ref_monstre.id_fk_type_ref_monstre = type_monstre.id_type_monstre')
		->where('ref_monstre.id_fk_taille_ref_monstre = taille_monstre.id_taille_monstre')
		->where('type_monstre.id_fk_type_groupe_monstre = type_groupe_monstre.id_type_groupe_monstre');
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}
}
