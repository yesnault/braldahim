<?php

class Competence extends Zend_Db_Table
{
    protected $_name = 'competence';
    protected $_dependentTables = array('hobbits_competences');
}