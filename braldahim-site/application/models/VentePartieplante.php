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
class VentePartieplante extends Zend_Db_Table {
	protected $_name = 'vente_partieplante';
	protected $_primary = array('id_vente_partieplante');

	function findByIdVente($idVente) {

		$nomChamp = "id_fk_vente_partieplante";
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
		$select->from('vente_partieplante', '*')
		->from('type_partieplante', '*')
		->from('type_plante', '*')
		->from('vente')
		->from('braldun', array('nom_braldun', 'prenom_braldun', 'id_braldun'))
		->where('id_fk_vente_partieplante = id_vente')
		->where('id_fk_braldun_vente = id_braldun')
		->where('id_fk_vente_partieplante = '.$liste)
		->where('id_fk_type_vente_partieplante = id_type_partieplante')
		->where('id_fk_type_plante_vente_partieplante = id_type_plante')
		->order(array('date_fin_vente desc', 'nom_type_plante', 'nom_type_partieplante'));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
	function findByIdType($idTypePlante, $idTypePartiePlante) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('vente_partieplante', '*')
		->from('type_partieplante', '*')
		->from('type_plante', '*')
		->from('vente')
		->from('braldun', array('nom_braldun', 'prenom_braldun', 'id_braldun'))
		->where('id_fk_vente_partieplante = id_vente')
		->where('id_fk_braldun_vente = id_braldun')
		->where('id_fk_type_vente_partieplante = id_type_partieplante')
		->where('id_fk_type_plante_vente_partieplante = ?', $idTypePlante)
		->where('id_fk_type_vente_partieplante = ?', $idTypePartiePlante)
		->where('id_fk_type_plante_vente_partieplante = id_type_plante')
		->order(array('date_fin_vente desc', 'nom_type_plante', 'nom_type_partieplante'));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}
