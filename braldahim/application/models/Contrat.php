<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Contrat extends Zend_Db_Table {
	protected $_name = 'contrat';
	protected $_primary = array('id_contrat');

	function findEnCoursByIdBraldunSourceAndCible($idBraldunSource, $idBraldunCible) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('contrat', '*')
				->where('id_fk_braldun_contrat = ?', intval($idBraldunSource))
				->where('id_fk_cible_braldun_contrat = ?', intval($idBraldunCible))
				->where('date_fin_contrat is null')
				->where('etat_contrat like ?', 'en cours');
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findByIdBraldun($idBraldun) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('contrat', '*')
				->from('braldun', '*')
				->where('id_fk_braldun_contrat = ?', intval($idBraldun))
				->where('id_fk_cible_braldun_contrat = id_braldun');
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findEnCoursByIdBraldun($idBraldun) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('contrat', '*')
				->from('braldun', '*')
				->where('id_fk_braldun_contrat = ?', intval($idBraldun))
				->where('id_fk_braldun_contrat = id_braldun')
				->where('date_fin_contrat is null')
				->where('etat_contrat like ?', 'en cours');
		$sql = $select->__toString();

		$result = $db->fetchAll($sql);
		if (count($result) > 1) {
			throw new Zend_Exception("Contrat::findEnCoursByIdBraldun nbInvalide:" . count($result) . " h:" . $idBraldun);
		} elseif (count($result) == 1) {
			return $result[0];
		} else {
			return null;
		}
	}

	function findEnCoursByIdBraldunCible($idBraldunCible) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('contrat', '*')
				->where('id_fk_cible_braldun_contrat = ?', intval($idBraldunCible))
				->where('date_fin_contrat is null')
				->where('etat_contrat like ?', 'en cours');
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findByIdBraldunAndIdContrat($idBraldun, $idContrat) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('contrat', '*')
				->where('id_fk_braldun_contrat = ?', intval($idBraldun))
				->where('id_contrat = ?', intval($idContrat));
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}

}
