<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: TypePotion.php 595 2008-11-09 11:21:27Z yvonnickesnault $
 * $Author: yvonnickesnault $
 * $LastChangedDate: 2008-11-09 12:21:27 +0100 (Sun, 09 Nov 2008) $
 * $LastChangedRevision: 595 $
 * $LastChangedBy: yvonnickesnault $
 */
class TypePotion extends Zend_Db_Table {
	protected $_name = 'type_potion';
	protected $_primary = 'id_type_potion';
	
	function findDistinctType() {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('type_potion', 'distinct(bm_type_potion)');
		$select->order("bm_type_potion DESC");
		$select->group(array('bm_type_potion'));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}
