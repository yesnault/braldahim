<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class HistoriqueEquipement extends Zend_Db_Table {
	protected $_name = 'historique_equipement';
	protected $_primary = "id_historique_equipement";
	
	public function findByIdEquipement($idEquipement, $pageMin, $pageMax, $filtre){
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('historique_equipement', '*')
		->from('type_historique_equipement', '*')
		->where('historique_equipement.id_fk_type_historique_equipement = type_historique_equipement.id_type_historique_equipement')
		->where('historique_equipement.id_fk_historique_equipement = '.intval($idEquipement))
		->order('id_historique_equipement DESC')
		->limitPage($pageMin, $pageMax);
		if ($filtre <> -1) {
			$select->where('type_historique_equipement.id_type_historique_equipement = '.intval($filtre));
		}
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}
