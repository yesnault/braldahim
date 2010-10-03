<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class TypeEtape extends Zend_Db_Table {
	protected $_name = 'type_etape';
	protected $_primary = 'id_type_etape';

	public function fetchAllNonIntiatiqueSansMetier() {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('type_etape', '*')
		->where('est_metier_type_etape like ?',"non")
		->where('est_initiatique_type_etape like ?',"non");

		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	public function fetchAllNonIntiatiqueAvecIdsMetier($listId) {
		$liste = "";
		if (count($listId) < 1) {
			$liste = "";
		} else {
			foreach($listId as $id) {
				if ((int) $id."" == $id."") {
					if ($liste == "") {
						$liste = $id;
					} else {
						$liste = $liste." OR id_fk_metier_type_etape_metier =".$id;
					}
				}
			}
		}

		if ($liste != "") {
			$db = $this->getAdapter();
			$select = $db->select();
			$select->from('type_etape', '*')
			->from('metier', '*')
			->from('type_etape_metier')
			->where('est_initiatique_type_etape like ?',"non")
			->where('id_fk_metier_type_etape_metier = id_metier')
			->where('id_fk_etape_type_etape_metier = id_type_etape')
			->where('id_fk_metier_type_etape_metier='. $liste);
			$sql = $select->__toString();
			return $db->fetchAll($sql);
		} else {
			return null;
		}
	}
}
