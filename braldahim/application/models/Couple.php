<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Couple extends Zend_Db_Table {
	protected $_name = 'couple';
	protected $_primary = array('id_fk_m_braldun_couple', 'id_fk_f_braldun_couple');
	
	function findAllEnfantPossible() {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('couple', '*')
		->where('nb_enfants_couple < ?', 5)
		->where('est_valide_couple like ?', 'oui');
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
	function findConjoint($sexe, $idBraldun, $estAncien = false) {
		
		$ancien = "";
		$ancienKey = "";
		if ($estAncien === true) {
			$ancien = "ancien_";
			$ancienKey = "_ancien_braldun";
		}
		
		$db = $this->getAdapter();
		$select = $db->select();
		if ($sexe == 'masculin') {
			$select->from('couple', '*')
			->from($ancien.'braldun', '*')
			->where('id_fk_f_braldun_couple = id_braldun'.$ancienKey)
			->where('id_fk_m_braldun_couple = ?', (int)$idBraldun);
		} else {
			$select->from('couple', '*')
			->from($ancien.'braldun', '*')
			->where('id_fk_m_braldun_couple = id_braldun'.$ancienKey)
			->where('id_fk_f_braldun_couple = ?', (int)$idBraldun);
		}
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}
