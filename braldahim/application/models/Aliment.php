<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: $
 * $Author: $
 * $LastChangedDate: $
 * $LastChangedRevision: $
 * $LastChangedBy: $
 */
class Aliment extends Zend_Db_Table {
	protected $_name = 'aliment';
	protected $_primary = array('id_aliment');
	
	const ID_TYPE_LAGER = 24;
	const ID_TYPE_ALE = 25;
	const ID_TYPE_STOUT = 26;
}
