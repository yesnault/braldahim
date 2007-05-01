<?php

class Zone extends Zend_Db_Table
{
    protected $_name = 'zone';
    
    function selectVue($x_min, $y_min, $x_max, $y_max) {
//		$where = ' x_min_zone <= '.$x_max;
//		c
//		$where .= ' AND y_min_zone >= '.$y_min;
//		$where .= ' AND y_max_zone <= '.$y_max;
		
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('zone', '*')
		->from('environnement', '*')
		->where('x_min_zone <= ?',$x_max)
		->where('x_max_zone >= ?',$x_min)
		->where('y_min_zone >= ?',$y_min)
		->where('y_max_zone <= ?',$y_max)
		->where('zone.id_fk_environnement_zone = environnement.id');
		$sql = $select->__toString();
		
		return $db->fetchAll($sql);
    }
}