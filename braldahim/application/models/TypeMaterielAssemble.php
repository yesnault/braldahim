<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class TypeMaterielAssemble extends Zend_Db_Table {
	protected $_name = 'type_materiel_assemble';
	protected $_primary = array('id_base_type_materiel_assemble', 'id_supplement_type_materiel_assemble');

	function findByIdTypeBase($idTypeBase) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('type_materiel_assemble', '*')
		->from('type_materiel', '*')
		->where('id_supplement_type_materiel_assemble = type_materiel.id_type_materiel')
		->where('id_base_type_materiel_assemble = ?', $idTypeBase)
		->order('nom_type_materiel ASC');

		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}

	function findByIdListTypeBase($listId) {

		$liste = "";
		if (count($listId) < 1) {
			$liste = "";
		} else {
			foreach($listId as $id) {
				if ((int) $id."" == $id."") {
					if ($liste == "") {
						$liste = $id;
					} else {
						$liste = $liste." OR id_base_type_materiel_assemble =".$id;
					}
				}
			}
		}

		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('type_materiel_assemble', '*')
		->from('type_materiel', '*')
		->where('id_supplement_type_materiel_assemble = type_materiel.id_type_materiel')
		->where('id_base_type_materiel_assemble = '.$liste)
		->order('nom_type_materiel ASC');

		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}

}
