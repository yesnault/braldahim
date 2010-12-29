<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class LotPrixMinerai extends Zend_Db_Table {
	protected $_name = 'lot_prix_minerai';
	protected $_primary = array("id_fk_type_lot_prix_minerai","id_fk_lot_prix_minerai");
	
    function findByIdLot($idLot) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('lot_prix_minerai', '*')
		->from('type_minerai', '*')
		->where('id_fk_lot_prix_minerai', (int)$idLot)
		->where('lot_prix_minerai.id_fk_type_lot_prix_minerai = type_minerai.id_type_minerai');
		$sql = $select->__toString();
		return $db->fetchAll($sql);
    }
}
