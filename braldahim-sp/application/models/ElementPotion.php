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
class ElementPotion extends Zend_Db_Table
{
	protected $_name = 'element_potion';
	protected $_primary = array('id_element_potion');

	function selectVue($x_min, $y_min, $x_max, $y_max, $z)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('element_potion', '*')
			->from('type_potion')
			->from('type_qualite')
			->from('potion')
			->where('id_element_potion = id_potion')
			->where('id_fk_type_potion = id_type_potion')
			->where('id_fk_type_qualite_potion = id_type_qualite')
			->where('x_element_potion <= ?', $x_max)
			->where('x_element_potion >= ?', $x_min)
			->where('y_element_potion <= ?', $y_max)
			->where('y_element_potion >= ?', $y_min)
			->where('z_element_potion = ?', $z);
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findByCase($x, $y, $z)
	{
		return $this->selectVue($x, $y, $x, $y, $z);
	}
}
