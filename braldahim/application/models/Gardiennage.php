<?php

class Gardiennage extends Zend_Db_Table {
    protected $_name = 'gardiennage';
	protected $_referenceMap    = array(
        'Hobbitgarde' => array(
            'columns'           => array('id_hobbit_gardiennage'),
            'refTableClass'     => 'Hobbit',
            'refColumns'        => array('id')
        ),
        'Gardien' => array(
            'columns'           => array('id_gardien_gardiennage'),
            'refTableClass'     => 'Hobbit',
            'refColumns'        => array('id')
        )
	);
	
	/**
	 * Renvoie tous les gardiens du hobbit passe en parametre.
	 */
    function findGardiens($id_hobbit_garde) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('gardiennage', 'id_gardien_gardiennage')
		->from('hobbit', 'nom_hobbit')
		->where('gardiennage.id_gardien_gardiennage = hobbit.id')
		->group('id_gardien_gardiennage', 'nom_hobbit');
		$sql = $select->__toString();

		return $db->fetchAll($sql);
    }
}