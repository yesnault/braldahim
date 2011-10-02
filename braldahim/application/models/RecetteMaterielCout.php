<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class RecetteMaterielCout extends Zend_Db_Table
{
	protected $_name = 'recette_materiel_cout';
	protected $_primary = array('id_fk_type_materiel_recette_materiel_cout',
		'id_fk_type_recette_materiel_cout');

	function findByIdTypeMateriel($idTypeMateriel)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('recette_materiel_cout', '*')
			->where('id_fk_type_materiel_recette_materiel_cout = ?', $idTypeMateriel);
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

}
