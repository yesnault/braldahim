<?php

class RangCommunaute extends Zend_Db_Table {
	protected $_name = 'rang_communaute';
	protected $_primary = array('id_fk_type_rang_communaute', 'id_fk_communaute_rang_communaute');
}
