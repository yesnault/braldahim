<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class TypeLot extends Zend_Db_Table {
	protected $_name = 'type_lot';
	protected $_primary = "id_type_lot";

	const ID_TYPE_RESERVATION_COMMUNAUTE_BRALDUN = 1;
	const ID_TYPE_RESERVATION_COMMUNAUTE_TOUS = 2;
	const ID_TYPE_VENTE_ECHOPPE_BRALDUN = 3;
	const ID_TYPE_VENTE_ECHOPPE_TOUS = 4;
	const ID_TYPE_VENTE_HOTEL = 5;

}
