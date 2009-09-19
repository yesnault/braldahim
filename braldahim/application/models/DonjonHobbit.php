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
class DonjonHobbit extends Zend_Db_Table {
	protected $_name = 'donjon_hobbit';
	protected $_primary = array('id_fk_hobbit_donjon_hobbit', 'id_fk_equipe_donjon_hobbit');

	public function findByIdHobbitAndIdEquipe($idHobbit, $idDonjonEquipe) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('donjon_hobbit', '*')
		->where('id_fk_hobbit_donjon_hobbit = ?', intval($idHobbit))
		->where("id_fk_equipe_donjon_hobbit = ?", intval($idDonjonEquipe));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

}