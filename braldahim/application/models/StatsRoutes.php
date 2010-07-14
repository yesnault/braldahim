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
class StatsRoutes extends Zend_Db_Table {
	protected $_name = 'stats_routes';
	protected $_primary = array('id_stats_routes');

	function findTopPalmaresBraldun($dateDebut, $dateFin) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('braldun', array('nom_braldun', 'prenom_braldun', 'id_braldun', 'niveau_braldun'));
		$select->from('stats_routes', array('sum(nb_stats_routes) as nombre'));
		$select->where('id_fk_braldun_stats_routes = id_braldun');
		$select->where('mois_stats_routes >= ?', $dateDebut);
		$select->where('mois_stats_routes < ?', $dateFin);
		$select->where('id_fk_metier_stats_routes = ?', Bral_Util_Metier::METIER_BUCHERON_ID);
		$select->order(array("nombre DESC"));
		$select->group(array('nom_braldun', 'prenom_braldun', 'id_braldun', 'niveau_braldun'));
		$select->limit(1, 0);
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);
		if ($resultat == null || count($resultat) < 1) {
			return null;
		}
		$nombre = $resultat[0]["nombre"];

		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('braldun', array('nom_braldun', 'prenom_braldun', 'id_braldun', 'niveau_braldun'));
		$select->from('stats_routes', array('sum(nb_stats_routes) as nombre'));
		$select->where('id_fk_braldun_stats_routes = id_braldun');
		$select->where('mois_stats_routes >= ?', $dateDebut);
		$select->where('mois_stats_routes < ?', $dateFin);
		$select->where('id_fk_metier_stats_routes = ?', Bral_Util_Metier::METIER_BUCHERON_ID);
		$select->order(array("nombre DESC"));
		$select->group(array('nom_braldun', 'prenom_braldun', 'id_braldun', 'niveau_braldun'));
		$select->having('nombre = ?', $nombre);
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function insertOrUpdate($data) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('stats_routes',
			'count(*) as nombre, 
			nb_stats_routes as quantiteRoute')
		->where('niveau_braldun_stats_routes = '.$data["niveau_braldun_stats_routes"]
		.' AND id_fk_braldun_stats_routes = '.$data["id_fk_braldun_stats_routes"]
		.' AND id_fk_metier_stats_routes = '.$data["id_fk_metier_stats_routes"]
		.' AND mois_stats_routes = \''.$data["mois_stats_routes"].'\'')
		->group(array('quantiteRoute'));
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		if (!isset($data["nb_stats_routes"])) {
			$data["nb_stats_routes"] = 0;
		}

		if (count($resultat) == 0) { // insert
			$this->insert($data);
		} else { // update
			$nombre = $resultat[0]["nombre"];
			$quantiteRoute = $resultat[0]["quantiteRoute"];

			$dataUpdate['nb_stats_routes'] = $quantiteRoute;

			if (isset($data["nb_stats_routes"])) {
				$dataUpdate['nb_stats_routes'] = $quantiteRoute + $data["nb_stats_routes"];
				if ($dataUpdate['nb_stats_routes'] < 0) {
					$dataUpdate['nb_stats_routes'] = 0;
				}
			}

			$where = 'niveau_braldun_stats_routes = '.$data["niveau_braldun_stats_routes"]
			.' AND id_fk_braldun_stats_routes = '.$data["id_fk_braldun_stats_routes"]
			.' AND id_fk_metier_stats_routes = '.$data["id_fk_metier_stats_routes"]
			.' AND mois_stats_routes = \''.$data["mois_stats_routes"].'\'';
			$this->update($dataUpdate, $where);
		}
	}
}