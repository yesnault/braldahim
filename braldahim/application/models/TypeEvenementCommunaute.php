<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class TypeEvenementCommunaute extends Zend_Db_Table {
	protected $_name = 'type_evenement_communaute';
	protected $_primary = 'id_type_evenement_communaute';

	const ID_TYPE_ARRIVEE_MEMBRE = 1;
	const ID_TYPE_DEPART_MEMBRE = 2;
	const ID_TYPE_DEPOT = 3;
	const ID_TYPE_RETRAIT_LOT = 4;
	const ID_TYPE_CREATION_LOT = 5;
	const ID_TYPE_ACHAT_LOT = 6;
	const ID_TYPE_INITIALISATION_BATIMENT = 7;
	const ID_TYPE_INITIALISATION_DEPENDANCE = 8;
	const ID_TYPE_ENTRETIEN = 9;
	const ID_TYPE_AMELIORATION = 10;
	const ID_TYPE_GESTIONNAIRE = 11;
	const ID_TYPE_ACCEPTATION_MEMBRE = 12;
	const ID_TYPE_RANG_MEMBRE = 13;
	const ID_TYPE_RANG_LIBELLE = 14;
}
