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
class TypeEtapeMetier extends Zend_Db_Table {
	protected $_name = 'type_etape_metier';
	protected $_primary = array('id_fk_etape_type_etape_metier', 'id_fk_metier_type_etape_metier');
}
