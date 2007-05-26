<?php

class Competence extends Zend_Db_Table {
    protected $_name = 'competence';
    protected $_primary = 'id_competence';
    protected $_dependentTables = array('hobbits_competences');
    
	public function findBasiques(){ 
		$where = $this->getAdapter()->quoteInto("type_competence = ?", "basic"); 
		return $this->fetchRow($where); 
	} 
}