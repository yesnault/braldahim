<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class EchoppePotion extends Zend_Db_Table {
	protected $_name = 'echoppe_potion';
	protected $_primary = "id_echoppe_potion";

	public function findByIdEchoppe($idEchoppe, $idTypePotion = null, $idPotion = null) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('echoppe_potion', '*')
		->from('type_potion')
		->from('type_qualite')
		->from('potion')
		->where('id_echoppe_potion = id_potion')
		->where('id_fk_type_potion = id_type_potion')
		->where('id_fk_type_qualite_potion = id_type_qualite')
		->where('id_fk_echoppe_echoppe_potion = ?', $idEchoppe)
		->order(array('type_potion ASC', 'nom_type_potion ASC', 'niveau_potion ASC', 'id_type_qualite ASC', 'id_echoppe_potion ASC'));
		if ($idTypePotion != null) {
			$select->where('id_type_potion = ?', intval($idTypePotion));
		}
		if ($idPotion != null) {
			$select->where('id_potion = ?', intval($idPotion));
		}

		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}
