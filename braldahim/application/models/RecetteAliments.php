<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class RecetteAliments extends Zend_Db_Table
{
	protected $_name = 'recette_aliments';
	protected $_primary = array('id_fk_type_aliment_recette_aliments', 'id_fk_type_ingredient_recette_aliments');

	function findByIdTypeAliment($idTypeAliment)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('recette_aliments', '*')
			->from('type_aliment', '*')
			->from('type_ingredient', '*')
			->where('id_fk_type_aliment_recette_aliments = ?', $idTypeAliment)
			->where('id_fk_type_ingredient_recette_aliments = id_type_ingredient')
			->where('id_fk_type_aliment_recette_aliments = id_type_aliment');

		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}
