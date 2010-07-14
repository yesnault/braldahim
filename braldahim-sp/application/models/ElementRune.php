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
class ElementRune extends Zend_Db_Table {
	protected $_name = 'element_rune';
	protected $_primary = 'id_rune_element_rune';

	function selectVue($x_min, $y_min, $x_max, $y_max, $z) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('element_rune', '*')
		->from('type_rune', '*')
		->from('rune', '*')
		->where('id_rune_element_rune = id_rune')
		->where('id_fk_type_rune = id_type_rune')
		->where('x_element_rune <= ?',$x_max)
		->where('x_element_rune >= ?',$x_min)
		->where('y_element_rune <= ?',$y_max)
		->where('y_element_rune >= ?',$y_min)
		->where('z_element_rune = ?',$z);
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}

	function findByCase($x, $y, $z) {
		return $this->selectVue($x, $y, $x, $y, $z);
	}
}