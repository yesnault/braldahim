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
class TypeCategorie extends Zend_Db_Table {
	protected $_name = 'type_categorie';
	protected $_primary = 'id_type_categorie';

	const ID_TYPE_BOURLINGUEUR = 1;
	const ID_TYPE_ECUMEUR = 2;
	const ID_TYPE_VOYAGEUR = 3;
	const ID_TYPE_SPECIAL = 4;
	const ID_TYPE_SOULE = 5;
	const ID_TYPE_PALMARES = 6;
	const ID_TYPE_REPUTATION = 7;
}