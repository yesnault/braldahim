<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class ElementMateriel extends Zend_Db_Table
{
	protected $_name = 'element_materiel';
	protected $_primary = 'id_element_materiel';

	function selectVue($x_min, $y_min, $x_max, $y_max, $z)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('element_materiel', '*')
			->from('type_materiel', '*')
			->from('materiel', '*')
			->where('id_element_materiel = id_materiel')
			->where('id_fk_type_materiel = id_type_materiel')
			->where('x_element_materiel <= ?', $x_max)
			->where('x_element_materiel >= ?', $x_min)
			->where('y_element_materiel <= ?', $y_max)
			->where('y_element_materiel >= ?', $y_min)
			->where('z_element_materiel = ?', $z);

		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findByCase($x, $y, $z)
	{
		return $this->selectVue($x, $y, $x, $y, $z);
	}
}