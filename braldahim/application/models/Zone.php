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
class Zone extends Zend_Db_Table {
	protected $_name = 'zone';
	protected $_primary = 'id_zone';

	function selectVue($x_min, $y_min, $x_max, $y_max) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('zone', '*')
		->from('environnement', '*')
		->where('x_min_zone <= ?',$x_max)
		->where('x_max_zone >= ?',$x_min)
		->where('y_min_zone <= ?',$y_max)
		->where('y_max_zone >= ?',$y_min)
		->where('zone.id_fk_environnement_zone = environnement.id_environnement');
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}

	function findByCase($x, $y) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('zone', '*')
		->from('environnement', '*')
		->where('x_min_zone <= ?',$x)
		->where('x_max_zone >= ?',$x)
		->where('y_min_zone <= ?',$y)
		->where('y_max_zone >= ?',$y)
		->where('zone.id_fk_environnement_zone = environnement.id_environnement');
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}

	public function fetchAllAvecEnvironnement() {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('environnement', '*')
		->from('zone', '*')
		->where('zone.id_fk_environnement_zone = environnement.id_environnement')
		->order('zone.id_zone');
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}
}