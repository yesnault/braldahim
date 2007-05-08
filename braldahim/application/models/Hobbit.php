<?php

class Hobbit extends Zend_Db_Table {
    protected $_name = 'hobbit';
    
    protected $_dependentTables = array('hobbits_competences');
    
    function selectVue($x_min, $y_min, $x_max, $y_max) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('hobbit', '*')
		->where('x_hobbit <= ?',$x_max)
		->where('x_hobbit >= ?',$x_min)
		->where('y_hobbit >= ?',$y_min)
		->where('y_hobbit <= ?',$y_max);
		
		$sql = $select->__toString();
		
		return $db->fetchAll($sql);
    }
}

