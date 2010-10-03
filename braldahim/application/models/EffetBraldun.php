<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class EffetBraldun extends Zend_Db_Table {
	protected $_name = 'effet_braldun';
	protected $_primary = array('id_effet_braldun');

	function findByIdBraldunCible($idBraldun) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('effet_braldun', '*')
		->where('id_fk_braldun_cible_effet_braldun = ?', intval($idBraldun));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findByIdBraldunCibleAndTypeEffet($idBraldun, $tabType) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('effet_braldun', '*')
		->where('id_fk_braldun_cible_effet_braldun = ?', intval($idBraldun));

		$nomChamp = 'caract_effet_braldun';
		$liste = "";
		if (count($tabType) < 1) {
			$liste = "";
		} else {
			foreach($tabType as $caract) {
				if ($liste == "") {
					$liste = "'".$caract."'";
				} else {
					$liste = $liste." OR ".$nomChamp." like '".$caract."'";
				}
			}
		}
		if ($liste != "") {
			$select->where($nomChamp .' like '. $liste);
		}
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findByIdEffetBraldun($idEffetBraldun) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('effet_braldun', '*')
		->where('id_effet_braldun = ?', intval($idEffetBraldun));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function enleveUnTour($effet) {
		Bral_Util_Log::potion()->debug('EffetBraldun - enleveUnTour - enter');
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('effet_braldun', '*')
		->where('id_effet_braldun = ?', intval($effet["id_effet_braldun"]));

		$sql = $select->__toString();
		$resultat = $db->fetchRow($sql);

		$retour = false;

		if ($resultat != null) {
			$resultat["nb_tour_restant_effet_braldun"] = $resultat["nb_tour_restant_effet_braldun"] - 1;
			Bral_Util_Log::potion()->debug('EffetBraldun - enleveUnTour - potion '.$effet["id_effet_braldun"].' tour(s) restant(s)='.$resultat["nb_tour_restant_effet_braldun"]);

			$where = 'id_effet_braldun = '.intval($effet["id_effet_braldun"]);
			if ($resultat["nb_tour_restant_effet_braldun"] < 0) {
				Bral_Util_Log::potion()->debug('EffetBraldun - enleveUnTour - suppression de l\'effet '.$effet["id_effet_braldun"].' de la table EffetBraldun');
				$this->delete($where);
				$retour = true;
			} else {
				Bral_Util_Log::potion()->debug('EffetBraldun - enleveUnTour - mise a jour de l\'effet '.$effet["id_effet_braldun"].' de la table EffetBraldun');
				$dataUpdate["nb_tour_restant_effet_braldun"] = $resultat["nb_tour_restant_effet_braldun"];
				$this->update($dataUpdate, $where);
				$retour = false;
			}
		}
		$texte = "false";
		if ($retour == true) {
			$texte = "true";
		}
		Bral_Util_Log::potion()->debug('EffetBraldun - enleveUnTour - exit - ('.$texte.')');
		return $retour;
	}
}
