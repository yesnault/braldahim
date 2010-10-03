<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class TypeIngredient extends Zend_Db_Table {
	protected $_name = 'type_ingredient';
	protected $_primary = 'id_type_ingredient';

	const ID_TYPE_VIANDE_FRAICHE = 8;
	const ID_TYPE_ORGE = 13;
	const ID_TYPE_HOUBLON = 14;

	public function findById($id){
		$where = $this->getAdapter()->quoteInto('id_type_ingredient = ?',(int)$id);
		return $this->fetchRow($where);
	}
}