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
class Role extends Zend_Db_Table {
	protected $_name = 'role';
	protected $_primary = 'id_role';
	protected $_dependentTables = array('bralduns_roles');
}