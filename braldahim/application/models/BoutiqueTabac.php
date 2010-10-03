<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class BoutiqueTabac extends Zend_Db_Table {
	protected $_name = 'boutique_tabac';
	protected $_primary = array('id_boutique_tabac');

	function findByIdLieu($id_lieu) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('boutique_tabac', '*')
		->from('type_tabac', '*')
		->where('id_fk_lieu_boutique_tabac = '.intval($id_lieu))
		->where('boutique_tabac.id_fk_type_boutique_tabac = type_tabac.id_type_tabac');
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}
}
