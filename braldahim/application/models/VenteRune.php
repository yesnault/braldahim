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
class VenteRune extends Zend_Db_Table {
	protected $_name = 'vente_rune';
	protected $_primary = array('id_rune_vente_rune');

	function findByIdVente($idVente) {
			
		$nomChamp = "id_fk_vente_rune";
		$liste = "";
		if (!is_array($idVente)) {
			$liste = intval($idVente);
		} else {
			foreach($idVente as $id) {
				if ((int) $id."" == $id."") {
					if ($liste == "") {
						$liste = $id;
					} else {
						$liste = $liste." OR ".$nomChamp."=".$id;
					}
				}
			}
		}

		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('vente_rune', '*')
		->from('type_rune', '*')
		->from('vente')
		->from('hobbit', array('nom_hobbit', 'prenom_hobbit', 'id_hobbit'))
		->where('id_fk_hobbit_vente = id_hobbit')
		->where('id_fk_vente_rune = id_vente')
		->where('id_fk_vente_rune = '.$liste)
		->where('vente_rune.id_fk_type_vente_rune = type_rune.id_type_rune')
		->order('date_fin_vente desc');
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findByIdType($idType) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('vente_rune', '*')
		->from('type_rune')
		->from('vente')
		->from('hobbit', array('nom_hobbit', 'prenom_hobbit', 'id_hobbit'))
		->where('id_fk_vente_rune = id_vente')
		->where('id_fk_hobbit_vente = id_hobbit')
		->where('id_fk_type_vente_rune = id_type_rune')
		->where('id_fk_type_vente_rune = ?', $idType)
		->where('est_identifiee_vente_rune = ?', 'oui')
		->order('date_fin_vente desc');
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
	function findNonIdentifiee() {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('vente_rune', '*')
		->from('type_rune')
		->from('vente')
		->from('hobbit', array('nom_hobbit', 'prenom_hobbit', 'id_hobbit'))
		->where('id_fk_vente_rune = id_vente')
		->where('id_fk_hobbit_vente = id_hobbit')
		->where('id_fk_type_vente_rune = id_type_rune')
		->where('est_identifiee_vente_rune = ?', 'non')
		->order('date_fin_vente desc');
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}
