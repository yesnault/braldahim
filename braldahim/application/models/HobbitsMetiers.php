<?php

class HobbitsMetiers extends Zend_Db_Table
{
	protected $_name = 'hobbits_metiers';
	protected $_referenceMap    = array(
	'Hobbit' => array(
	'columns'           => array('id_hobbit_hmetier'),
	'refTableClass'     => 'Hobbit',
	'refColumns'        => array('id')
	),
	'Metier' => array(
	'columns'           => array('id_metier_hmetier'),
	'refTableClass'     => 'Metier',
	'refColumns'        => array('id')
	)
	);

	public function findMetiersByHobbitId($idHobbit) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('hobbits_metiers', '*')
		->from('metier', '*')
		->where('hobbits_metiers.id_metier_hmetier = metier.id')
		->where('hobbits_metiers.id_hobbit_hmetier = '.intval($idHobbit));
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}
}