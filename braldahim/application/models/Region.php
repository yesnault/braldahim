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
class Region extends Zend_Db_Table {
	protected $_name = 'region';
	protected $_primary = 'id_region';

	function selectVue($x_min, $y_min, $x_max, $y_max) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('region', '*')
		->where('x_min_region <= ?',$x_max)
		->where('x_max_region >= ?',$x_min)
		->where('y_min_region <= ?',$y_max)
		->where('y_max_region >= ?',$y_min);
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}
}