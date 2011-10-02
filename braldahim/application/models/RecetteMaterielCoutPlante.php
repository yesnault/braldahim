<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class RecetteMaterielCoutPlante extends Zend_Db_Table
{
	protected $_name = 'recette_materiel_cout_plante';
	protected $_primary = array('id_fk_type_materiel_recette_materiel_cout_plante', 'id_fk_type_plante_recette_materiel_cout_plante', 'id_fk_type_partieplante_recette_materiel_cout_plante');

	function findByIdTypeMateriel($idTypeMateriel)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('recette_materiel_cout_plante', '*')
			->from('type_plante', '*')
			->from('type_partieplante', '*')
			->where('id_fk_type_materiel_recette_materiel_cout_plante = ?', $idTypeMateriel)
			->where('id_fk_type_plante_recette_materiel_cout_plante = id_type_plante')
			->where('id_fk_type_partieplante_recette_materiel_cout_plante = id_type_partieplante');

		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}
}
