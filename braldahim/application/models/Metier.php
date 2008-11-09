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
class Metier extends Zend_Db_Table {
    protected $_name = 'metier';
    protected $_primary = 'id_metier';
    protected $_dependentTables = array('hobbits_metiers');
}