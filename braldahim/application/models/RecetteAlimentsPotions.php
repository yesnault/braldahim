<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class RecetteAlimentsPotions extends Zend_Db_Table {
	protected $_name = 'recette_aliments_potions';
	protected $_primary = array('id_fk_type_aliment_recette_aliments_potions', 'id_fk_type_ingredient_recette_aliments_potions');

	function findByIdTypeAliment($idTypeAliment) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('recette_aliments_potions', '*')
		->from('type_aliment', '*')
		->from('type_potion', '*')
		->where('id_fk_type_aliment_recette_aliments_potions = ?', $idTypeAliment)
		->where('id_fk_type_aliment_recette_aliments_potions = id_type_aliment')
		->where('id_fk_type_potion_recette_aliments_potions = id_type_potion');

		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}
