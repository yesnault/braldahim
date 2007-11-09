<?php

class LabanRune extends Zend_Db_Table {
	protected $_name = 'laban_rune';
	protected $_primary = array('id_laban_rune', 'id_hobbit_laban_rune');
	
    function findByIdHobbit($id_hobbit, $identifiee = null) {
    	$whereIdentifiee = "";
    	if ($identifiee != null) {
    		$whereIdentifiee = " AND laban_rune.est_identifiee_rune = '".$identifiee."'";
    	}
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('laban_rune', '*')
		->from('type_rune', '*')
		->where('id_hobbit_laban_rune = '.intval($id_hobbit))
		->where('laban_rune.id_fk_type_laban_rune = type_rune.id_type_rune'.$whereIdentifiee);
		$sql = $select->__toString();

		return $db->fetchAll($sql);
    }
}
