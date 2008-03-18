<?php

class EffetMotD extends Zend_Db_Table {
	protected $_name = 'effet_mot_d';
	protected $_primary = array('id_fk_hobbit_effet_mot_d', 'id_fk_type_monstre_effet_mot_d');

}
