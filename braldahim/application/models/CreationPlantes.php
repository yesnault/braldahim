<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class CreationPlantes extends Zend_Db_Table {
	protected $_name = 'creation_plantes';
	protected $_primary = array('id_fk_type_plante_creation_plantes', 'id_fk_environnement_creation_plantes');

}
