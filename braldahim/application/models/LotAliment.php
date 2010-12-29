<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class LotAliment extends Zend_Db_Table {
	protected $_name = 'lot_aliment';
	protected $_primary = array('id_lot_aliment');

	function findByIdLot($idLot) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('lot_aliment', '*')
		->from('type_aliment')
		->from('type_qualite')
		->from('aliment', '*')
		->where('id_aliment = id_lot_aliment')
		->where('id_fk_type_aliment = id_type_aliment')
		->where('id_fk_type_qualite_aliment = id_type_qualite')
		->where('id_fk_lot_lot_aliment = ?', intval($idLot));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}
