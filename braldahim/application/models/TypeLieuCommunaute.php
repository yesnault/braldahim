<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class TypeLieuCommunaute extends Zend_Db_Table {
	protected $_name = 'type_lieu_communaute';
	protected $_primary = 'id_type_lieu_communaute';

	const ID_TYPE_AGRICULTURE = 1;
	const ID_TYPE_ARTS = 2;
	const ID_TYPE_COMMERCE = 3;
	const ID_TYPE_MEDECINE = 4;
	const ID_TYPE_MILITAIRE = 5;
	const ID_TYPE_POLITIQUE = 6;
	const ID_TYPE_RECHERCHE = 7;
}