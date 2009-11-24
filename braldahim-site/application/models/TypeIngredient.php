<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: TypeIngredient.php 2019 2009-09-19 10:32:13Z yvonnickesnault $
 * $Author: yvonnickesnault $
 * $LastChangedDate: 2009-09-19 12:32:13 +0200 (sam., 19 sept. 2009) $
 * $LastChangedRevision: 2019 $
 * $LastChangedBy: yvonnickesnault $
 */
class TypeIngredient extends Zend_Db_Table {
	protected $_name = 'type_ingredient';
	protected $_primary = 'id_type_ingredient';
	
	const ID_TYPE_VIANDE_FRAICHE = 8;
}