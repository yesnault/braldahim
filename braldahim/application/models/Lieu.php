<?php

class Lieu extends Zend_Db_Table {
    protected $_name = 'lieu';
    
    public function findByType($type){ 
		$where = $this->getAdapter()->quoteInto('id_fk_type_lieu = ?',$type); 
		return $this->fetchAll($where); 
	} 
}