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
class TypePlante extends Zend_Db_Table {
	protected $_name = 'type_plante';
	protected $_primary = 'id_type_plante';
	
	public function fetchAllAvecEnvironnement() {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('type_plante', '*')
		->from('environnement', '*')
		->where('type_plante.id_fk_environnement_type_plante = environnement.id_environnement');
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}
	
	public function findAll() {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('type_plante', '*')
		->order(array("categorie_type_plante", "nom_type_plante"));
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}
	
}