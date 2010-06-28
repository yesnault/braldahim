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
class TypeAliment extends Zend_Db_Table {
	protected $_name = 'type_aliment';
	protected $_primary = "id_type_aliment";

	const ID_TYPE_RAGOUT = 1;
	const ID_TYPE_LAGER = 24;
	const ID_TYPE_ALE = 25;
	const ID_TYPE_STOUT = 26;
	const ID_TYPE_JOUR_MILIEU = 27;

	public function findById($id){
		$where = $this->getAdapter()->quoteInto('id_type_aliment = ?',(int)$id);
		return $this->fetchRow($where);
	}

	function findAllByType($type) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('type_aliment', '*')
		->where('type_type_aliment = ?', $type)
		->order('nom_type_aliment');

		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}
