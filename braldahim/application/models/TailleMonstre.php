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
class TailleMonstre extends Zend_Db_Table {
	protected $_name = 'taille_monstre';
	protected $_primary = "id_taille_monstre";
	
	const ID_TAILLE_PETIT = 1;
	const ID_TAILLE_NORMAL = 2;
	const ID_TAILLE_GRAND = 3;
	const ID_TAILLE_GIGANTESQUE = 4;
}
