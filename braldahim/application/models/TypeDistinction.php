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
class TypeDistinction extends Zend_Db_Table {
	protected $_name = 'type_distinction';
	protected $_primary = 'id_type_distinction';

	public function findByIdFkTypeLieu($idLieu){
		$where = $this->getAdapter()->quoteInto('id_fk_lieu_type_distinction = ?',(int)$idLieu);
		return $this->fetchRow($where);
	}

	function findDistinctionsByIdTypeDistinction($idTypeDistinction) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('type_distinction', '*')
		->from('type_categorie', '*')
		->where('id_fk_type_categorie_distinction = id_type_categorie')
		->where('id_type_distinction = ?', intval($idTypeDistinction))
		->order(array('ordre_type_categorie'));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}
