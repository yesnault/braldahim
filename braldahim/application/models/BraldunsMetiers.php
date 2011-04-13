<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class BraldunsMetiers extends Zend_Db_Table
{
	protected $_name = 'bralduns_metiers';
	protected $_referenceMap    = array(
		'Braldun' => array(
			'columns'           => array('id_fk_braldun_hmetier'),
			'refTableClass'     => 'Braldun',
			'refColumns'        => array('id')
	),
		'Metier' => array(
			'columns'           => array('id_fk_metier_hmetier'),
			'refTableClass'     => 'Metier',
			'refColumns'        => array('id_metier')
	)
	);

	public function findMetiersByBraldunId($idBraldun) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('bralduns_metiers', '*')
		->from('metier', '*')
		->where('bralduns_metiers.id_fk_metier_hmetier = metier.id_metier')
		->where('bralduns_metiers.id_fk_braldun_hmetier = '.intval($idBraldun))
		->order('metier.nom_masculin_metier');
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}

	public function findMetiersByBraldunIdList($listIdBraldun) {

		if ($listIdBraldun == null) {
			return null;
		}
		
		$nomChamp = "bralduns_metiers.id_fk_braldun_hmetier";
		$liste = "";
		
		foreach($listIdBraldun as $id) {
			if ((int) $id."" == $id."") {
				if ($liste == "") {
					$liste = $id;
				} else {
					$liste = $liste." OR ".$nomChamp."=".$id;
				}
			}
		}

		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('bralduns_metiers', '*')
		->from('metier', '*')
		->where('bralduns_metiers.id_fk_metier_hmetier = metier.id_metier')
		->where($nomChamp .'='. $liste)
		->order('bralduns_metiers.id_fk_braldun_hmetier');

		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}

	public function findMetierCourantByBraldunId($idBraldun) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('bralduns_metiers', '*')
		->from('metier', '*')
		->where('bralduns_metiers.id_fk_metier_hmetier = metier.id_metier')
		->where('bralduns_metiers.id_fk_braldun_hmetier = '.intval($idBraldun))
		->where('est_actif_hmetier = ?', 'oui')
		->order('metier.nom_masculin_metier');
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}

	public function findMetiersEchoppeByBraldunId($idBraldun) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('bralduns_metiers', '*')
		->from('metier', '*')
		->where('bralduns_metiers.id_fk_metier_hmetier = metier.id_metier')
		->where('bralduns_metiers.id_fk_braldun_hmetier = '.intval($idBraldun))
		->where("metier.construction_echoppe_metier = 'oui'")
		->order('metier.nom_masculin_metier');
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}

	public function peutPossederEchoppeIdBraldun($idBraldun) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('bralduns_metiers', 'count(id_fk_metier_hmetier) as nombre')
		->from('metier', 'id_metier')
		->where("metier.construction_echoppe_metier = 'oui'")
		->where("bralduns_metiers.id_fk_braldun_hmetier = ".intval($idBraldun))
		->where("bralduns_metiers.id_fk_metier_hmetier = metier.id_metier")
		->group("id_metier");
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		if (!isset($resultat[0]) || $resultat[0]["nombre"] <1) {
			return false;
		} else {
			return true;
		}
	}
}