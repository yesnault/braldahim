<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id:$
 * $Author:$
 * $LastChangedDate:$
 * $LastChangedRevision:$
 * $LastChangedBy:$
 */
class RecettePotions extends Zend_Db_Table {
	protected $_name = 'recette_potions';
	protected $_primary = array('id_fk_type_potion_recette_potion', 'id_fk_type_plante_recette_potion', 'id_fk_type_partieplante_recette_potion');

	function findByIdTypePotion($idTypePotion) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('recette_potions', '*')
		->from('type_plante', '*')
		->from('type_partieplante', '*')
		->where('id_fk_type_potion_recette_potion = ?',$idTypePotion)
		->where('id_fk_type_plante_recette_potion = id_type_plante')
		->where('id_fk_type_partieplante_recette_potion = id_type_partieplante');

		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}
}
