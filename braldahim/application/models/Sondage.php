<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: $
 * $Author: $
 * $LastChangedDate: 2009-09-24 13:43:22 +0200 (Jeu, 24 sep 2009) $
 * $LastChangedRevision: $
 * $LastChangedBy: $
 */
class Sondage extends Zend_Db_Table {
	protected $_name = 'sondage';
	protected $_primary = "id_sondage";

	public function findEnCours() {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('sondage', '*');
		$select->where("etat_sondage like ?", 'EN_COURS');
		$sql = $select->__toString();
		$result = $db->fetchAll($sql);
		return $result;
	}

	public function findATerminer() {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('sondage', '*');
		$select->where("etat_sondage like ?", 'EN_COURS');
		$select->where("date_fin_sondage <= ?" , date("Y-m-d H:i:s"));
		$sql = $select->__toString();
		$result = $db->fetchAll($sql);
		return $result;
	}

	public function findADebuter() {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('sondage', '*');
		$select->where("etat_sondage like ?", 'NON_DEBUTE');
		$select->where("date_debut_sondage <= ?" , date("Y-m-d H:i:s"));
		$sql = $select->__toString();
		$result = $db->fetchAll($sql);
		return $result;
	}
}
