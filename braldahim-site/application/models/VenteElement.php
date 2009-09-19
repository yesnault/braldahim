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
class VenteElement extends Zend_Db_Table {
	protected $_name = 'vente_element';
	protected $_primary = array('id_vente_element');

	function findByIdVente($idVente) {
			
		$nomChamp = "id_fk_vente_element";
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
		$select->from('vente_element', '*')
		->from('vente')
		->from('hobbit', array('nom_hobbit', 'prenom_hobbit', 'id_hobbit'))
		->where('id_fk_vente_element = id_vente')
		->where('id_fk_hobbit_vente = id_hobbit')
		->where('id_fk_vente_element = '.$liste)
		->order('date_fin_vente desc');
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
	function findByType($type) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('vente_element', '*')
		->from('vente')
		->from('hobbit', array('nom_hobbit', 'prenom_hobbit', 'id_hobbit'))
		->where('id_fk_vente_element = id_vente')
		->where('id_fk_hobbit_vente = id_hobbit')
		->where('type_vente_element = ?', $type)
		->order('date_fin_vente desc');
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}