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
class Jetable extends Zend_Db_Table {
	protected $_name = 'jetable';
	protected $_primary = 'id_jetable';

	public function countByNom($nom){
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('jetable', 'count(*) as nombre')
		->where('lcase(nom_jetable) like ?', (string)mb_strtolower(trim($nom)));
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		$nombre = $resultat[0]["nombre"];
		return $nombre;
	}
}
