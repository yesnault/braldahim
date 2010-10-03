<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class RecetteMaterielCoutMinerai extends Zend_Db_Table {
	protected $_name = 'recette_materiel_cout_minerai';
	protected $_primary = array('id_fk_type_materiel_recette_materiel_cout_minerai',
								'id_fk_type_recette_materiel_cout_minerai');

	function findByIdTypeMateriel($idTypeMateriel) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('recette_materiel_cout_minerai', '*')
		->from('type_minerai', '*')
		->where('id_fk_type_materiel_recette_materiel_cout_minerai = '.intval($idTypeMateriel))
		->where('recette_materiel_cout_minerai.id_fk_type_recette_materiel_cout_minerai = type_minerai.id_type_minerai');
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}