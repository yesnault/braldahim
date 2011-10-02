<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class TypeEvenement extends Zend_Db_Table
{
	protected $_name = 'type_evenement';
	protected $_primary = 'id_type_evenement';

	const ID_TYPE_NAISSANCE = 1;
	const ID_TYPE_KO = 2;
	const ID_TYPE_DEPLACEMENT = 3;
	const ID_TYPE_COMPETENCE = 4;
	const ID_TYPE_KILLMONSTRE = 5;
	const ID_TYPE_DON = 6;
	const ID_TYPE_SERVICE = 7;
	const ID_TYPE_RAMASSER = 8;
	const ID_TYPE_ATTAQUER = 9;
	const ID_TYPE_EFFET = 10;
	const ID_TYPE_ECHOPPE = 11;
	const ID_TYPE_BOUTIQUE = 12;
	const ID_TYPE_DEPOSER = 13;
	const ID_TYPE_EVENEMENT = 14;
	const ID_TYPE_KOBRALDUN = 15;
	const ID_TYPE_SOULE = 16;
	const ID_TYPE_TRANSBAHUTER = 17;
	const ID_TYPE_FAMILLE = 18;
	const ID_TYPE_SPECIAL = 19;
	const ID_TYPE_RECHERCHE = 20;
	const ID_TYPE_KILLGIBIER = 21;

}
