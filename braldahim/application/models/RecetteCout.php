<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id$
 * $Author$
 * $LastChangedDate$
 * $LastChangedRevision$
 * $LastChangedBy$
 */
class RecetteCout extends Zend_Db_Table {
	protected $_name = 'recette_cout';
	protected $_primary = array('id_fk_type_equipement_recette_cout',
								'id_fk_type_recette_cout',
								'niveau_recette_cout'); 
	
	function findByIdTypeEquipement($idTypeEquipement) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('recette_cout', '*')
		->where('id_fk_type_equipement_recette_cout = ?',$idTypeEquipement);
		
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}
	
	function findByIdTypeEquipementAndNiveau($idTypeEquipement, $niveau) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('recette_cout', '*')
		->where('id_fk_type_equipement_recette_cout = ?',$idTypeEquipement)
		->where('niveau_recette_cout = ?',$niveau);
		
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}
}
