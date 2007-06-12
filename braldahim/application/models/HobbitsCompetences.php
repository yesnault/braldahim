<?php

class HobbitsCompetences extends Zend_Db_Table {
    protected $_name = 'hobbits_competences';
	protected $_referenceMap    = array(
        'Hobbit' => array(
            'columns'           => array('id_hobbit_hcomp'),
            'refTableClass'     => 'Hobbit',
            'refColumns'        => array('id_hobbit')
        ),
        'Competence' => array(
            'columns'           => array('id_competence_hcomp'),
            'refTableClass'     => 'Competence',
            'refColumns'        => array('id_competence')
        )
	);
	
    function findByIdHobbit($id_hobbit) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('hobbits_competences', '*')
		->from('competence', '*')
		->where('hobbits_competences.id_hobbit_hcomp = '.intval($id_hobbit))
		->where('hobbits_competences.id_competence_hcomp = competence.id_competence');
		$sql = $select->__toString();
		return $db->fetchAll($sql);
    }
}