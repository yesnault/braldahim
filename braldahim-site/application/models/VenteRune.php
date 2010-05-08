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
		->from('rune')
		->from('braldun', array('nom_braldun', 'prenom_braldun', 'id_braldun'))
		->where('id_rune_vente_rune = id_rune')
		->where('id_fk_braldun_vente = id_braldun')
		->where('id_fk_vente_rune = id_vente')
		->where('id_fk_vente_rune = '.$liste)
		->where('id_fk_type_rune = id_type_rune')
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
		->from('rune')
		->from('braldun', array('nom_braldun', 'prenom_braldun', 'id_braldun'))
		->where('id_fk_vente_rune = id_vente')
		->where('id_fk_braldun_vente = id_braldun')
		->where('id_rune_vente_rune = id_rune')
		->where('id_fk_type_rune = id_type_rune')
		->where('id_fk_type_rune = ?', $idType)
		->where('est_identifiee_rune = ?', 'oui')
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
		->from('rune')
		->from('braldun', array('nom_braldun', 'prenom_braldun', 'id_braldun'))
		->where('id_fk_vente_rune = id_vente')
		->where('id_fk_braldun_vente = id_braldun')
		->where('id_rune_vente_rune = id_rune')
		->where('id_fk_type_rune = id_type_rune')
		->where('est_identifiee_rune = ?', 'non')
		->order('date_fin_vente desc');
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}
