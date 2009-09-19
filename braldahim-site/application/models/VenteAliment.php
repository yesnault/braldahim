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
class VenteAliment extends Zend_Db_Table {
	protected $_name = 'vente_aliment';
	protected $_primary = array('id_vente_aliment');

	function findByIdVente($idVente) {
			
		$nomChamp = "id_fk_vente_aliment";
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
		$select->from('vente_aliment', '*')
		->from('type_aliment')
		->from('type_qualite')
		->from('vente')
		->from('hobbit', array('nom_hobbit', 'prenom_hobbit', 'id_hobbit'))
		->where('id_fk_vente_aliment = id_vente')
		->where('id_fk_hobbit_vente = id_hobbit')
		->where('id_fk_type_vente_aliment = id_type_aliment')
		->where('id_fk_type_qualite_vente_aliment = id_type_qualite')
		->where('id_fk_vente_aliment = '.$liste)
		->order('date_fin_vente desc');
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findByIdType($idType) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('vente_aliment', '*')
		->from('type_aliment')
		->from('type_qualite')
		->from('vente')
		->from('hobbit', array('nom_hobbit', 'prenom_hobbit', 'id_hobbit'))
		->where('id_fk_vente_aliment = id_vente')
		->where('id_fk_hobbit_vente = id_hobbit')
		->where('id_fk_type_vente_aliment = id_type_aliment')
		->where('id_fk_type_qualite_vente_aliment = id_type_qualite')
		->where('id_fk_type_vente_aliment = ?', $idType)
		->order('date_fin_vente desc');
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}