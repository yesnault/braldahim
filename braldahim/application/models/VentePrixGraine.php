<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class VentePrixGraine extends Zend_Db_Table {
	protected $_name = 'vente_prix_graine';
	protected $_primary = array("id_fk_type_vente_prix_graine","id_fk_vente_prix_graine");
	
    function findByIdVente($idVente) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('vente_prix_graine', '*')
		->from('type_graine', '*')
		->where('id_fk_vente_prix_graine', (int)$idVente)
		->where('vente_prix_graine.id_fk_type_vente_prix_graine = type_graine.id_type_graine');
		$sql = $select->__toString();
		return $db->fetchAll($sql);
    }
}
