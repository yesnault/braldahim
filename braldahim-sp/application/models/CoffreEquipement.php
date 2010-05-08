<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: CoffreEquipement.php 1906 2009-08-18 22:01:32Z yvonnickesnault $
 * $Author: yvonnickesnault $
 * $LastChangedDate: 2009-08-19 00:01:32 +0200 (mer., 19 août 2009) $
 * $LastChangedRevision: 1906 $
 * $LastChangedBy: yvonnickesnault $
 */
class CoffreEquipement extends Zend_Db_Table {
	protected $_name = 'coffre_equipement';
	protected $_primary = array('id_coffre_equipement');

	function findByIdBraldun($id_braldun) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('coffre_equipement', '*')
		->from('recette_equipements')
		->from('type_equipement')
		->from('type_qualite')
		->from('type_emplacement')
		->from('type_piece')
		->from('equipement')
		->from('type_ingredient')
		->where('id_type_ingredient = id_fk_type_ingredient_base_type_equipement')
		->where('id_equipement = id_coffre_equipement')
		->where('id_fk_recette_equipement = id_recette_equipement')
		->where('id_fk_type_recette_equipement = id_type_equipement')
		->where('id_fk_type_qualite_recette_equipement = id_type_qualite')
		->where('id_fk_type_emplacement_recette_equipement = id_type_emplacement')
		->where('id_fk_type_piece_type_equipement = id_type_piece')
		->where('id_fk_braldun_coffre_equipement = ?', intval($id_braldun))
		->joinLeft('mot_runique','id_fk_mot_runique_equipement = id_mot_runique');
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}
