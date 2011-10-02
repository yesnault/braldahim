<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class AncienBraldun extends Zend_Db_Table
{
	protected $_name = 'ancien_braldun';
	protected $_primary = 'id_ancien_braldun';

	public function findById($id)
	{
		$where = $this->getAdapter()->quoteInto('id_braldun_ancien_braldun = ?', (int)$id);
		return $this->fetchRow($where);
	}
}
