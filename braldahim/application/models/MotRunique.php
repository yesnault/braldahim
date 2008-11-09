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
class MotRunique extends Zend_Db_Table {
	protected $_name = 'mot_runique';
	protected $_primary = array('id_mot_runique');

	function findByIdTypePieceAndRunes($id_fk_type_piece, $tab_runes) {
		
		$where = "";
		$indice = 0;
		if ($tab_runes != null && count($tab_runes) > 0) {
			foreach($tab_runes as $k => $v) {
				$indice++; 
				$where .= " AND id_fk_type_rune_".$indice."_mot_runique = ".$v["id_fk_type_rune_laban_rune"];
			}
		}
			
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('mot_runique', '*')
		->where('id_fk_type_piece_mot_runique = '.intval($id_fk_type_piece). $where);
		
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}
