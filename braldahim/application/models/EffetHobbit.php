<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: $
 * $Author: $
 * $LastChangedDate: $
 * $LastChangedRevision: $
 * $LastChangedBy: $
 */
class EffetHobbit extends Zend_Db_Table {
	protected $_name = 'effet_hobbit';
	protected $_primary = array('id_effet_hobbit');

	function findByIdHobbitCible($idHobbit) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('effet_hobbit', '*')
		->where('id_fk_hobbit_cible_effet_hobbit = ?', intval($idHobbit));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findByIdHobbitCibleAndTypeEffet($idHobbit, $tabType) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('effet_hobbit', '*')
		->where('id_fk_hobbit_cible_effet_hobbit = ?', intval($idHobbit));

		$nomChamp = 'caract_effet_hobbit';
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

	function findByIdEffetHobbit($idEffetHobbit) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('effet_hobbit', '*')
		->where('id_effet_hobbit = ?', intval($idEffetHobbit));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function enleveUnTour($effet) {
		Bral_Util_Log::potion()->debug('EffetHobbit - enleveUnTour - enter');
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('effet_hobbit', '*')
		->where('id_effet_hobbit = ?', intval($effet["id_effet_hobbit"]));

		$sql = $select->__toString();
		$resultat = $db->fetchRow($sql);

		$retour = false;

		if ($resultat != null) {
			$resultat["nb_tour_restant_effet_hobbit"] = $resultat["nb_tour_restant_effet_hobbit"] - 1;
			Bral_Util_Log::potion()->debug('EffetHobbit - enleveUnTour - potion '.$effet["id_effet_hobbit"].' tour(s) restant(s)='.$resultat["nb_tour_restant_effet_hobbit"]);

			$where = 'id_effet_hobbit = '.intval($effet["id_effet_hobbit"]);
			if ($resultat["nb_tour_restant_effet_hobbit"] < 0) {
				Bral_Util_Log::potion()->debug('EffetHobbit - enleveUnTour - suppression de l\'effet '.$effet["id_effet_hobbit"].' de la table EffetHobbit');
				$this->delete($where);
				$retour = true;
			} else {
				Bral_Util_Log::potion()->debug('EffetHobbit - enleveUnTour - mise a jour de l\'effet '.$effet["id_effet_hobbit"].' de la table EffetHobbit');
				$dataUpdate["nb_tour_restant_effet_hobbit"] = $resultat["nb_tour_restant_effet_hobbit"];
				$this->update($dataUpdate, $where);
				$retour = false;
			}
		}
		$texte = "false";
		if ($retour == true) {
			$texte = "true";
		}
		Bral_Util_Log::potion()->debug('EffetHobbit - enleveUnTour - exit - ('.$texte.')');
		return $retour;
	}
}
