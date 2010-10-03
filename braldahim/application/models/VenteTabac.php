<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class VenteTabac extends Zend_Db_Table {
	protected $_name = 'vente_tabac';
	protected $_primary = array('id_vente_tabac');

	function findByIdBraldun($idVente) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('vente_tabac', '*')
		->from('type_tabac', '*')
		->where('id_fk_vente_tabac = '.intval($idVente))
		->where('vente_tabac.id_fk_type_vente_tabac = type_tabac.id_type_tabac');
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}
}
