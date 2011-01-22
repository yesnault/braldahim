<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class CoffreMateriel extends Zend_Db_Table {
	protected $_name = 'coffre_materiel';
	protected $_primary = array('id_coffre_materiel');

	function findByIdConteneur($idCoffre) {
		return $this->findByIdCoffre($idCoffre);
	}

	function findByIdCoffre($idCoffre) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('coffre_materiel', '*')
		->from('type_materiel', '*')
		->from('materiel', '*')
		->where('id_coffre_materiel = id_materiel')
		->where('id_fk_type_materiel = id_type_materiel')
		->where('id_fk_coffre_coffre_materiel = ?', intval($idCoffre));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}
