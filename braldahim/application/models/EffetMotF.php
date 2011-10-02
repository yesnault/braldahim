<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class EffetMotF extends Zend_Db_Table
{
	protected $_name = 'effet_mot_f';
	protected $_primary = array('id_fk_braldun_effet_mot_f', 'id_fk_type_monstre_effet_mot_f');

}
