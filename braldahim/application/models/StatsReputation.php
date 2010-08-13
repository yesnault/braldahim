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
class StatsReputation extends Zend_Db_Table {
	protected $_name = 'stats_reputation';
	protected $_primary = array('id_stats_reputation');

	function findTopPalmaresBraldun($dateDebut, $dateFin, $type = "gredin") {
		if ($type == "gredin") {
			$champ = "points_gredin_stats_reputation";
		} else {
			$champ = "points_redresseur_stats_reputation";
		}

		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('braldun', array('nom_braldun', 'prenom_braldun', 'id_braldun', 'niveau_braldun'));
		$select->from('stats_reputation', 'SUM('.$champ.') as nombre');
		$select->where('id_fk_braldun_stats_reputation = id_braldun');
		$select->where('mois_stats_reputation >= ?', $dateDebut);
		$select->where('mois_stats_reputation < ?', $dateFin);
		$select->order("nombre DESC");
		$select->group(array('nom_braldun', 'prenom_braldun', 'id_braldun', 'niveau_braldun'));
		$select->limit(1, 0);
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);
		if ($resultat == null || count($resultat) < 1 || $resultat[0]["nombre"] < 1) {
			return null;
		}
		$nombre = $resultat[0]["nombre"];

		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('braldun', array('nom_braldun', 'prenom_braldun', 'id_braldun', 'niveau_braldun'));
		$select->from('stats_reputation', 'SUM('.$champ.') as nombre');
		$select->where('id_fk_braldun_stats_reputation = id_braldun');
		$select->where('mois_stats_reputation >= ?', $dateDebut);
		$select->where('mois_stats_reputation < ?', $dateFin);
		$select->order("nombre DESC");
		$select->group(array('nom_braldun', 'prenom_braldun', 'id_braldun', 'niveau_braldun'));
		$select->having('nombre = ?', $nombre);
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function deleteAndInsert($data) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('stats_reputation', 'count(*) as nombre, points_redresseur_stats_reputation as quantitePxPerso, points_gredin_stats_reputation as quantitePxCommun')
		->where('niveau_braldun_stats_reputation = '.$data["niveau_braldun_stats_reputation"].' AND id_fk_braldun_stats_reputation = '.$data["id_fk_braldun_stats_reputation"]. ' AND mois_stats_reputation = \''.$data["mois_stats_reputation"].'\'')
		->group(array('quantitePxPerso', 'quantitePxCommun'));
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		if (!isset($data["points_gredin_stats_reputation"])) {
			$data["points_gredin_stats_reputation"] = 0;
		}

		if (!isset($data["points_redresseur_stats_reputation"])) {
			$data["points_redresseur_stats_reputation"] = 0;
		}

		$where = 'id_fk_braldun_stats_reputation = '.$data["id_fk_braldun_stats_reputation"]. ' AND mois_stats_reputation = \''.$data["mois_stats_reputation"].'\'';
		$this->delete($where);
		$this->insert($data);
	}
}