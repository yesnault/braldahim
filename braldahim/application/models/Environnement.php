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
class Environnement extends Zend_Db_Table {
	protected $_name = 'environnement';
	protected $_primary = 'id_environnement';

	function findAllQuete() {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('environnement', '*')
		->where('est_quete_environnement like ?', "oui");
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}