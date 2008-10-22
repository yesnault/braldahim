<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id:$
 * $Author:$
 * $LastChangedDate:$
 * $LastChangedRevision:$
 * $LastChangedBy:$
 */
class Evenement extends Zend_Db_Table {
	protected $_name = 'evenement';
	protected $_primary = 'id_evenement';

	public function findByIdHobbit($idHobbit, $pageMin, $pageMax, $filtre){
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('evenement', '*')
		->from('type_evenement', '*')
		->where('evenement.id_fk_type_evenement = type_evenement.id_type_evenement')
		->where('evenement.id_fk_hobbit_evenement = '.intval($idHobbit))
		->order('id_evenement DESC')
		->limitPage($pageMin, $pageMax);
		if ($filtre <> -1) {
			$select->where('type_evenement.id_type_evenement = '.$filtre);
		}
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}