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
class VentePrixMinerai extends Zend_Db_Table {
	protected $_name = 'vente_prix_minerai';
	protected $_primary = array("id_fk_type_vente_prix_minerai","id_fk_vente_prix_minerai");
	
	function insertOrUpdate($data) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from(
		'vente_prix_minerai', 
		'count(*) as nombre, prix_vente_prix_minerai as prix')
		->where('id_fk_type_vente_prix_minerai = ?',$data["id_fk_type_vente_prix_minerai"])
		->where('id_fk_vente_prix_minerai = ?',$data["id_fk_vente_prix_minerai"])
		->group(array('prix'));
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		if (count($resultat) == 0) { // insert
			$this->insert($data);
		} else { // update
			$nombre = $resultat[0]["nombre"];
			$prix = $resultat[0]["prix"];
			
			$prix = $prix + $data["prix_vente_prix_minerai"];
			if ($prix < 0) $prix = 0;
			
			$dataUpdate = array(
			'prix_vente_prix_minerai' => $prix,
			);
			$where = ' id_fk_type_vente_prix_minerai = '.$data["id_fk_type_vente_prix_minerai"];
			$where .= ' AND id_fk_vente_prix_minerai = '.$data["id_fk_vente_prix_minerai"];
			$this->update($dataUpdate, $where);
		}
	}
	
    function findByIdsEquipement($idVente) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('vente_prix_minerai', '*')
		->from('type_minerai', '*')
		->where('id_fk_vente_prix_minerai', (int)$idVente)
		->where('vente_prix_minerai.id_fk_type_vente_prix_minerai = type_minerai.id_type_minerai');
		$sql = $select->__toString();
		
		return $db->fetchAll($sql);
    }
}
