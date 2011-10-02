<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class IdsLot extends Zend_Db_Table
{
	protected $_name = 'ids_lot';
	protected $_primary = "id_ids_lot";

	public function prepareNext()
	{
		return $this->insert(array('date_creation_ids_lot' => date("Y-m-d H:i:s")));
	}
}
