<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class VenteHistorique extends Zend_Db_Table {
	protected $_name = 'vente_historique';
	protected $_primary = array('id_vente_historique');
}