<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class MotRunique extends Zend_Db_Table
{
	protected $_name = 'mot_runique';
	protected $_primary = array('id_mot_runique');

	function findByIdTypePieceAndRunes($nomSystemeTypePiece, $tabRunes)
	{

		$where = "";
		$nb = 0;
		if ($tabRunes != null && count($tabRunes) > 0) {
			foreach ($tabRunes as $k => $v) {
				$nb++;
				$where .= " AND id_fk_type_rune_" . $k . "_mot_runique = " . $v["id_fk_type_rune"];
			}
		}

		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('mot_runique', '*')
			->from('type_piece', null)
			->where('id_fk_type_piece_mot_runique = id_type_piece')
			->where("nb_total_rune_mot_runique = " . $nb . " AND nom_systeme_type_piece like '" . $nomSystemeTypePiece . "'" . $where);

		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findARegenerer($coefLune)
	{

		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('mot_runique', '*')
			->where("coef_lune_changement_mot_runique <= ?", $coefLune)
			->where("date_generation_mot_runique <= ?", Bral_Util_ConvertDate::get_date_add_day_to_date(date("Y-m-d H:i:s"), -30));

		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}
