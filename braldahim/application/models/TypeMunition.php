<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class TypeMunition extends Zend_Db_Table
{
	protected $_name = 'type_munition';
	protected $_primary = 'id_type_munition';

	public function findById($id)
	{
		$where = $this->getAdapter()->quoteInto('id_type_munition = ?', (int)$id);
		return $this->fetchRow($where);
	}
}
