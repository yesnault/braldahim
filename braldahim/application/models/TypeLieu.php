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
class TypeLieu extends Zend_Db_Table {
	protected $_name = 'type_lieu';
	protected $_primary = 'id_type_lieu';

	const ID_TYPE_MARIE = 1;
	const ID_TYPE_CENTREFORMATION = 2;
	const ID_TYPE_GARE = 3;
	const ID_TYPE_HOPITAL = 4;
	const ID_TYPE_BIBLIOTHEQUE = 5;
	const ID_TYPE_ACADEMIE = 6;
	const ID_TYPE_BANQUE = 7;
	const ID_TYPE_JOAILLIER = 8;
	const ID_TYPE_AUBERGE = 9;
	const ID_TYPE_BBOIS = 10;
	const ID_TYPE_BPARTIEPLANTES = 11;
	const ID_TYPE_BMINERAIS = 12;
	const ID_TYPE_TABATIERE = 13;
	const ID_TYPE_NOTAIRE = 14;
	const ID_TYPE_QUETE = 15;
	const ID_TYPE_ECHANGEURRUNE = 16;
	const ID_TYPE_ASSEMBLEUR = 17;
	const ID_TYPE_BPEAUX = 18;
	const ID_TYPE_HOTEL = 19;
	const ID_TYPE_POSTEDEGARDE = 20;
	const ID_TYPE_ENTREE_GROTTE = 21;
	const ID_TYPE_ESCALIERS = 22;
	const ID_TYPE_LIEUMYTHIQUE = 23;
}