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
class VentePrixPartiePlante extends Zend_Db_Table {
	protected $_name = 'vente_prix_partieplante';
	protected $_primary = array("id_fk_type_vente_prix_partieplante","id_fk_type_plante_vente_prix_partieplante", "id_fk_vente_prix_partieplante");

	function insertOrUpdate($data) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from(
		'vente_prix_partieplante', 
		'count(*) as nombre, prix_vente_prix_partieplante as prix')
		->where('id_fk_type_vente_prix_partieplante = ?',$data["id_fk_type_vente_prix_partieplante"])
		->where('id_fk_type_plante_vente_prix_partieplante = ?',$data["id_fk_type_plante_vente_prix_partieplante"])
		->where('id_fk_vente_prix_partieplante = ?',$data["id_fk_vente_prix_partieplante"])
		->group(array('prix'));
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		if (count($resultat) == 0) { // insert
			$this->insert($data);
		} else { // update
			$nombre = $resultat[0]["nombre"];
			$prix = $resultat[0]["prix"];
			
			$prix = $prix + $data["prix_vente_prix_partieplante"];
			if ($prix < 0) $prix = 0;
			
			$dataUpdate = array(
			'prix_vente_prix_partieplante' => $prix,
			);
			$where = ' id_fk_type_vente_prix_partieplante = '.$data["id_fk_type_vente_prix_partieplante"];
			$where .= ' AND id_fk_type_plante_vente_prix_partieplante = '.$data["id_fk_type_plante_vente_prix_partieplante"];
			$where .= ' AND id_fk_vente_prix_partieplante = '.$data["id_fk_vente_prix_partieplante"];
			$this->update($dataUpdate, $where);
		}
	}

   function findByIdVente($idVente) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('vente_prix_partieplante', '*')
		->from('type_partieplante', '*')
		->from('type_plante', '*')
		->where('id_fk_vente_prix_partieplante',(int)$idVente)
		->where('vente_prix_partieplante.id_fk_type_vente_prix_partieplante = type_partieplante.id_type_partieplante')
		->where('vente_prix_partieplante.id_fk_type_plante_vente_prix_partieplante = type_plante.id_type_plante');
		$sql = $select->__toString();
		
		return $db->fetchAll($sql);
    }
}
