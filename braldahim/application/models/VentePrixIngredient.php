<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class VentePrixIngredient extends Zend_Db_Table {
	protected $_name = 'vente_prix_ingredient';
	protected $_primary = array("id_fk_type_vente_prix_ingredient","id_fk_vente_prix_ingredient");
	
    function findByIdVente($idVente) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('vente_prix_ingredient', '*')
		->from('type_ingredient', '*')
		->where('id_fk_vente_prix_ingredient', (int)$idVente)
		->where('vente_prix_ingredient.id_fk_type_vente_prix_ingredient = type_ingredient.id_type_ingredient');
		$sql = $select->__toString();
		return $db->fetchAll($sql);
    }
}
