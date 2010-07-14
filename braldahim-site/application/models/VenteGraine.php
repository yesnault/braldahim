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
class VenteGraine extends Zend_Db_Table {
	protected $_name = 'vente_graine';
	protected $_primary = array('id_vente_graine');

	function findByIdVente($idVente) {
		$nomChamp = "id_fk_vente_graine";
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
		$select->from('vente_graine', '*')
		->from('type_graine', '*')
		->from('vente')
		->from('braldun', array('nom_braldun', 'prenom_braldun', 'id_braldun'))
		->where('id_fk_vente_graine = id_vente')
		->where('id_fk_braldun_vente = id_braldun')
		->where('id_fk_type_vente_graine = id_type_graine')
		->where('id_fk_vente_graine = '.$liste)
		->order('date_fin_vente desc');
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
	function findByIdType($idType) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('vente_graine', '*')
		->from('type_graine')
		->from('vente')
		->from('braldun', array('nom_braldun', 'prenom_braldun', 'id_braldun'))
		->where('id_fk_vente_graine = id_vente')
		->where('id_fk_braldun_vente = id_braldun')
		->where('id_fk_type_vente_graine = id_type_graine')
		->where('id_fk_type_vente_graine = ?', $idType)
		->order('date_fin_vente desc');
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

}
