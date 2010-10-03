<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class HistoriquePotion extends Zend_Db_Table {
	protected $_name = 'historique_potion';
	protected $_primary = "id_historique_potion";
	
	public function findByIdPotion($idPotion, $pageMin, $pageMax, $filtre){
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('historique_potion', '*')
		->from('type_historique_potion', '*')
		->where('historique_potion.id_fk_type_historique_potion = type_historique_potion.id_type_historique_potion')
		->where('historique_potion.id_fk_historique_potion = ?', intval($idPotion))
		->order('id_historique_potion DESC')
		->limitPage($pageMin, $pageMax);
		if ($filtre <> -1) {
			$select->where('type_historique_potion.id_type_historique_potion = ? ', intval($filtre));
		}
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}
