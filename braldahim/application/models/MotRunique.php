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

	function findByIdTypePieceAndRunes($nomSystemeTypePiece, $tabRunes) {
		
		$where = "";
		if ($tabRunes != null && count($tabRunes) > 0) {
			foreach($tabRunes as $k => $v) {
				$where .= " AND id_fk_type_rune_".$k."_mot_runique = ".$v["id_fk_type_rune_laban_rune"];
			}
		}
			
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('mot_runique', '*')
		->from('type_piece', null)
		->where('id_fk_type_piece_mot_runique = id_type_piece')
		->where("nom_systeme_type_piece like '".$nomSystemeTypePiece."'".$where);
		
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}
