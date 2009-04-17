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
class Couple extends Zend_Db_Table {
	protected $_name = 'couple';
	protected $_primary = array('id_fk_m_hobbit_couple', 'id_fk_f_hobbit_couple');
	
	function findAllEnfantPossible() {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('couple', '*')
		->where('nb_enfants_couple < ?', 5);
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
	function findConjoint($sexe, $idHobbit, $estAncien = false) {
		
		$ancien = "";
		if ($estAncien === true) {
			$ancien = "ancien_";
		}
		
		$db = $this->getAdapter();
		$select = $db->select();
		if ($sexe == 'masculin') {
			$select->from('couple', '*')
			->from($ancien.'hobbit', '*')
			->where('id_fk_f_hobbit_couple = id_'.$ancien.'hobbit')
			->where('id_fk_m_hobbit_couple = ?', (int)$idHobbit);
		} else {
			$select->from('couple', '*')
			->from($ancien.'hobbit', '*')
			->where('id_fk_m_hobbit_couple = id_'.$ancien.'hobbit')
			->where('id_fk_f_hobbit_couple = ?', (int)$idHobbit);
		}
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}
