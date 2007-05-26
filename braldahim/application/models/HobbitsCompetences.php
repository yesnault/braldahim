<?php

class HobbitsCompetences extends Zend_Db_Table
{
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
	
}