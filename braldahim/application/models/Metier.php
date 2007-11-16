<?php

class Metier extends Zend_Db_Table {
    protected $_name = 'metier';
    protected $_primary = 'id_metier';
    protected $_dependentTables = array('hobbits_metiers');
}