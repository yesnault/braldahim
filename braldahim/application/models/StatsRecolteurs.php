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
class StatsRecolteurs extends Zend_Db_Table {
	protected $_name = 'stats_recolteurs';
	protected $_primary = array('id_stats_recolteurs');

	function insertOrUpdate($data) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('stats_recolteurs',
			'count(*) as nombre, 
			nb_minerai_stats_recolteurs as quantiteMinerai, 
			nb_partieplante_stats_recolteurs as quantitePartiePlante,
			nb_peau_stats_recolteurs as quantitePeau,
			nb_viande_stats_recolteurs as quantiteViande,
			nb_bois_stats_recolteurs as quantiteBois')
		->where('niveau_hobbit_stats_recolteurs = '.$data["niveau_hobbit_stats_recolteurs"].' AND id_fk_hobbit_stats_recolteurs = '.$data["id_fk_hobbit_stats_recolteurs"]. ' AND mois_stats_recolteurs = \''.$data["mois_stats_recolteurs"].'\'')
		->group(array('quantiteMinerai', 'quantitePartiePlante', 'quantitePeau', 'quantiteViande', 'quantiteBois'));
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		if (!isset($data["nb_minerai_stats_recolteurs"])) {
			$data["nb_minerai_stats_recolteurs"] = 0;
		}

		if (!isset($data["nb_partieplante_stats_recolteurs"])) {
			$data["nb_partieplante_stats_recolteurs"] = 0;
		}

		if (!isset($data["nb_peau_stats_recolteurs"])) {
			$data["nb_peau_stats_recolteurs"] = 0;
		}

		if (!isset($data["nb_viande_stats_recolteurs"])) {
			$data["nb_viande_stats_recolteurs"] = 0;
		}

		if (!isset($data["nb_bois_stats_recolteurs"])) {
			$data["nb_bois_stats_recolteurs"] = 0;
		}

		if (count($resultat) == 0) { // insert
			$this->insert($data);
		} else { // update
			$nombre = $resultat[0]["nombre"];
			$quantiteMinerai = $resultat[0]["quantiteMinerai"];
			$quantitePartiePlante = $resultat[0]["quantitePartiePlante"];
			$quantitePeau = $resultat[0]["quantitePeau"];
			$quantiteViande = $resultat[0]["quantiteViande"];
			$quantiteBois = $resultat[0]["quantiteBois"];
				
			$dataUpdate['nb_minerai_stats_recolteurs'] = $quantiteMinerai;
			$dataUpdate['nb_partieplante_stats_recolteurs'] = $quantitePartiePlante;
			$dataUpdate['nb_peau_stats_recolteurs'] = $quantitePeau;
			$dataUpdate['nb_viande_stats_recolteurs'] = $quantiteViande;
			$dataUpdate['nb_bois_stats_recolteurs'] = $quantiteBois;
				
			if (isset($data["nb_minerai_stats_recolteurs"])) {
				$dataUpdate['nb_minerai_stats_recolteurs'] = $quantiteMinerai + $data["nb_minerai_stats_recolteurs"];
				if ($dataUpdate['nb_minerai_stats_recolteurs'] < 0) {
					$dataUpdate['nb_minerai_stats_recolteurs'] = 0;
				}
			}
				
			if (isset($data["nb_partieplante_stats_recolteurs"])) {
				$dataUpdate['nb_partieplante_stats_recolteurs'] = $quantitePartiePlante + $data["nb_partieplante_stats_recolteurs"];
				if ($dataUpdate['nb_partieplante_stats_recolteurs'] < 0) {
					$dataUpdate['nb_partieplante_stats_recolteurs'] = 0;
				}
			}
				
			if (isset($data["nb_peau_stats_recolteurs"])) {
				$dataUpdate['nb_peau_stats_recolteurs'] = $quantitePeau + $data["nb_peau_stats_recolteurs"];
				if ($dataUpdate['nb_peau_stats_recolteurs'] < 0) {
					$dataUpdate['nb_peau_stats_recolteurs'] = 0;
				}
			}
				
			if (isset($data["nb_viande_stats_recolteurs"])) {
				$dataUpdate['nb_viande_stats_recolteurs'] = $quantiteViande + $data["nb_viande_stats_recolteurs"];
				if ($dataUpdate['nb_viande_stats_recolteurs'] < 0) {
					$dataUpdate['nb_viande_stats_recolteurs'] = 0;
				}
			}
				
			if (isset($data["nb_bois_stats_recolteurs"])) {
				$dataUpdate['nb_bois_stats_recolteurs'] = $quantiteBois + $data["nb_bois_stats_recolteurs"];
				if ($dataUpdate['nb_bois_stats_recolteurs'] < 0) {
					$dataUpdate['nb_bois_stats_recolteurs'] = 0;
				}
			}
				
			$where = 'niveau_hobbit_stats_recolteurs = '.$data["niveau_hobbit_stats_recolteurs"].' AND id_fk_hobbit_stats_recolteurs = '.$data["id_fk_hobbit_stats_recolteurs"]. ' AND mois_stats_recolteurs = \''.$data["mois_stats_recolteurs"].'\'';
			$this->update($dataUpdate, $where);
		}
	}

	function findByHobbitAndDateAndIdTypeMetier($idHobbit, $dateDebut, $dateFin, $idTypeMetier) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('stats_recolteurs', $this->getSelectType($idTypeMetier));
		$select->where('mois_stats_recolteurs >= ?', $dateDebut);
		$select->where('mois_stats_recolteurs < ?', $dateFin);
		$select->where('id_fk_hobbit_stats_recolteurs = ?', (int)$idHobbit);
		$select->order("nombre DESC");
		$select->group(array('id_fk_hobbit_stats_recolteurs'));
		$select->limit(10, 0);
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
	private function getSelectType($idTypeMetier, $where=false) {
		Zend_Loader::loadClass("Bral_Util_Metier");
		
		$retour = "";
		switch($idTypeMetier) {
			case Bral_Util_Metier::METIER_MINEUR_ID :
				$retour = "SUM(nb_minerai_stats_recolteurs)";
				break;
			case Bral_Util_Metier::METIER_HERBORISTE_ID:
				$retour = "SUM(nb_partieplante_stats_recolteurs)";
				break;
			case Bral_Util_Metier::METIER_CHASSEUR_ID:
				$retour = "SUM(nb_peau_stats_recolteurs + nb_viande_stats_recolteurs)";
				break;
			case Bral_Util_Metier::METIER_BUCHERON_ID:
				$retour = "SUM(nb_bois_stats_recolteurs)";
				break;
		}
		if (!$where) {
			$retour .= " as nombre";
		}
		return $retour;
	}
}