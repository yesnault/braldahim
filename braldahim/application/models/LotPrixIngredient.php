<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class LotPrixIngredient extends Zend_Db_Table {
	protected $_name = 'lot_prix_ingredient';
	protected $_primary = array("id_fk_type_lot_prix_ingredient","id_fk_lot_prix_ingredient");
	
    function findByIdLot($idLot) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('lot_prix_ingredient', '*')
		->from('type_ingredient', '*')
		->where('id_fk_lot_prix_ingredient = ?', (int)$idLot)
		->where('lot_prix_ingredient.id_fk_type_lot_prix_ingredient = type_ingredient.id_type_ingredient');
		$sql = $select->__toString();
		return $db->fetchAll($sql);
    }
}
