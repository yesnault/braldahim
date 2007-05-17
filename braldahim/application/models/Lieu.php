<?php

class Lieu extends Zend_Db_Table {
    protected $_name = 'lieu';
    
    public function findByType($type){ 
		$where = $this->getAdapter()->quoteInto('id_fk_type_lieu = ?',$type); 
		return $this->fetchAll($where); 
	} 
	
    function selectVue($x_min, $y_min, $x_max, $y_max) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('lieu', '*')
		->from('type_lieu', '*')
		->where('x_lieu <= ?',$x_max)
		->where('x_lieu >= ?',$x_min)
		->where('y_lieu >= ?',$y_min)
		->where('y_lieu <= ?',$y_max)
		->where('lieu.id_fk_type_lieu = type_lieu.id');
		$sql = $select->__toString();

		return $db->fetchAll($sql);
    }
    
    function selectCase($x, $y) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('lieu', '*')
		->from('type_lieu', '*')
		->where('x_lieu = ?',$x)
		->where('y_lieu = ?',$y)
		->where('lieu.id_fk_type_lieu = type_lieu.id');
		$sql = $select->__toString();

		return $db->fetchAll($sql);
    }
}