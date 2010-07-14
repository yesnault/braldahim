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
class Script extends Zend_Db_Table {
	protected $_name = 'script';
	protected $_primary = array('id_script');


	public function countByDate($dateDebut, $dateFin, $etat = null) {
		$where = "";
		if ($etat != null) {
			$where = " etat_script = '".$etat. "' AND ";
		}

		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('script', 'count(*) as nombre')
		->where('date_debut_script >= ?', $dateDebut)
		->where($where.' date_fin_script <= ?', $dateFin);
		$sql = $select->__toString();
		$resultat =  $db->fetchAll($sql);
		return $resultat[0]["nombre"];
	}

	public function countByIdBraldunAndType($idBraldun, $type) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('script', 'count(*) as nombre')
		->where('id_fk_braldun_script = ?', intval($idBraldun))
		->where('type_script = ?', $type);
		$sql = $select->__toString();
		$resultat =  $db->fetchAll($sql);
		return $resultat[0]["nombre"];
	}

	public function countAll() {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('script', 'count(*) as nombre');
		$sql = $select->__toString();
		$resultat =  $db->fetchAll($sql);
		return $resultat[0]["nombre"];
	}

	public function countAllByEtat($etat) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('script', 'count(*) as nombre')
		->where('etat_script = ?', $etat);
		$sql = $select->__toString();
		$resultat =  $db->fetchAll($sql);
		return $resultat[0]["nombre"];
	}
}
