<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Testeur extends Zend_Db_Table {
	protected $_name = 'testeur';
	protected $_primary = array('id_testeur');

	public function findByEmail($email){
		$where = $this->getAdapter()->quoteInto('lcase(email_testeur) = ?',(string)strtolower(trim($email)));
		return $this->fetchRow($where);
	}
}
