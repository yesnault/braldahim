<?php

class Lieu extends Zend_Db_Table {
    protected $_name = 'lieu';
    
    public function findByType($type){ 
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('lieu', '*')
		->from('type_lieu', '*')
		->from('ville', '*')
		->where('lieu.id_fk_type_lieu = ?',$type)
		->where('lieu.id_fk_type_lieu = type_lieu.id')
		->where('lieu.id_fk_ville_lieu = ville.id');
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	} 
	
    function selectVue($x_min, $y_min, $x_max, $y_max) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('lieu', '*')
		->from('type_lieu', '*')
		->from('ville', '*')
		->where('x_lieu <= ?',$x_max)
		->where('x_lieu >= ?',$x_min)
		->where('y_lieu >= ?',$y_min)
		->where('y_lieu <= ?',$y_max)
		->where('lieu.id_fk_type_lieu = type_lieu.id')
		->where('lieu.id_fk_ville_lieu = ville.id');
		$sql = $select->__toString();

		return $db->fetchAll($sql);
    }
    
    function findCase($x, $y) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('lieu', '*')
		->from('type_lieu', '*')
		->from('ville', '*')
		->where('x_lieu = ?',$x)
		->where('y_lieu = ?',$y)
		->where('lieu.id_fk_type_lieu = type_lieu.id')
		->where('lieu.id_fk_ville_lieu = ville.id');
		$sql = $select->__toString();

		return $db->fetchAll($sql);
    }
}