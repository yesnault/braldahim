<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class TypeUnite extends Zend_Db_Table
{
	protected $_name = 'type_unite';
	protected $_primary = "id_type_unite";

	const ID_TYPE_CASTARS = 4;
	const NOM_SYSTEME_TYPE_CASTARS = "castar";
	const NOM_TYPE_CASTARS = "Castar";
	const NOM_TYPE_PLURIEL_CASTARS = "Castars";
}
