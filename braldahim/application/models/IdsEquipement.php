<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id$
 * $Author$
 * $LastChangedDate$
 * $LastChangedRevision$
 * $LastChangedBy$
 */
class IdsEquipement extends Zend_Db_Table {
	protected $_name = 'ids_equipement';
	protected $_primary = "id_ids_equipement";
	
	public function prepareNext() {
		return $this->insert(array('date_creation_ids_equipement' => date("Y-m-d H:i:s")));
	}
}
