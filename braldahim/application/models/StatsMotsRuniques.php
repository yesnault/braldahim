<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class StatsMotsRuniques extends Zend_Db_Table
{
	protected $_name = 'stats_mots_runiques';
	protected $_primary = array('id_stats_mots_runiques');

	function insertOrUpdate($data)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('stats_mots_runiques',
			'count(*) as nombre, 
			nb_piece_stats_mots_runiques as quantitePiece')
			->where('niveau_piece_stats_mots_runiques = ' . $data["niveau_piece_stats_mots_runiques"]
				. ' AND id_fk_mot_runique_stats_mots_runiques = ' . $data["id_fk_mot_runique_stats_mots_runiques"]
				. ' AND id_fk_type_piece_stats_mots_runiques = ' . $data["id_fk_type_piece_stats_mots_runiques"]
				. ' AND mois_stats_mots_runiques = \'' . $data["mois_stats_mots_runiques"] . '\'')
			->group(array('quantitePiece'));
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		if (!isset($data["nb_piece_stats_mots_runiques"])) {
			$data["nb_piece_stats_mots_runiques"] = 0;
		}

		if (count($resultat) == 0) { // insert
			$this->insert($data);
		} else { // update
			$nombre = $resultat[0]["nombre"];
			$quantitePiece = $resultat[0]["quantitePiece"];

			$dataUpdate['nb_piece_stats_mots_runiques'] = $quantitePiece;

			if (isset($data["nb_piece_stats_mots_runiques"])) {
				$dataUpdate['nb_piece_stats_mots_runiques'] = $quantitePiece + $data["nb_piece_stats_mots_runiques"];
				if ($dataUpdate['nb_piece_stats_mots_runiques'] < 0) {
					$dataUpdate['nb_piece_stats_mots_runiques'] = 0;
				}
			}

			$where = 'niveau_piece_stats_mots_runiques = ' . $data["niveau_piece_stats_mots_runiques"]
				. ' AND id_fk_mot_runique_stats_mots_runiques = ' . $data["id_fk_mot_runique_stats_mots_runiques"]
				. ' AND id_fk_type_piece_stats_mots_runiques = ' . $data["id_fk_type_piece_stats_mots_runiques"]
				. ' AND mois_stats_mots_runiques = \'' . $data["mois_stats_mots_runiques"] . '\'';
			$this->update($dataUpdate, $where);
		}
	}
}