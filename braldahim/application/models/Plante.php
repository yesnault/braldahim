<?php

class Plante extends Zend_Db_Table {
    protected $_name = 'plante';
    
    function selectVue($x_min, $y_min, $x_max, $y_max) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('plante', '*')
		->from('type_plante', '*')
		->where('x_plante <= ?',$x_max)
		->where('x_plante >= ?',$x_min)
		->where('y_plante >= ?',$y_min)
		->where('y_plante <= ?',$y_max)
		->where('plante.id_fk_type_plante = type_plante.id');
		$sql = $select->__toString();

		return $db->fetchAll($sql);
    }
    
   function countVue($x_min, $y_min, $x_max, $y_max) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('plante', 'count(*) as nombre')
		->where('x_plante <= ?',$x_max)
		->where('x_plante >= ?',$x_min)
		->where('y_plante >= ?',$y_min)
		->where('y_plante <= ?',$y_max);
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);
		
		$nombre = $resultat[0]["nombre"];
		return $nombre;
    }
    
    function findCase($x, $y) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('plante', '*')
		->from('type_plante', '*')
		->where('x_plante = ?',$x)
		->where('y_plante = ?',$y)
		->where('plante.id_fk_type_plante = type_plante.id');
		$sql = $select->__toString();

		return $db->fetchAll($sql);
    }
}