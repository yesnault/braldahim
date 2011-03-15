<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class EvenementCommunaute extends Zend_Db_Table {
	protected $_name = 'evenement_communaute';
	protected $_primary = 'id_evenement_communaute';

	public function findByIdCommunaute($idCommunaute, $pageMin, $pageMax, $filtre){
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('evenement_communaute', '*')
		->from('type_evenement_communaute', '*')
		->where('evenement_communaute.id_fk_type_evenement_communaute = type_evenement_communaute.id_type_evenement_communaute')
		->where('evenement_communaute.id_fk_communaute_evenement_communaute = ?', intval($idCommunaute))
		->order('id_evenement_communaute DESC')
		->limitPage($pageMin, $pageMax);
		if ($filtre <> -1) {
			$select->where('type_evenement_communaute.id_type_evenement_communaute = '.$filtre);
		}
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}