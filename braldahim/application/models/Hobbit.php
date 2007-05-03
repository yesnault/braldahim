<?php

class Hobbit extends Zend_Db_Table
{
    protected $_name = 'hobbit';
    
    protected $_dependentTables = array('hobbits_competences');
}

