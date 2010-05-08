<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: CoffrePartieplante.php 839 2008-12-26 21:35:54Z yvonnickesnault $
 * $Author: yvonnickesnault $
 * $LastChangedDate: 2008-12-26 22:35:54 +0100 (ven., 26 déc. 2008) $
 * $LastChangedRevision: 839 $
 * $LastChangedBy: yvonnickesnault $
 */
class CoffrePartieplante extends Zend_Db_Table {
	protected $_name = 'coffre_partieplante';
	protected $_primary = array('id_fk_type_coffre_partieplante', 'id_fk_braldun_coffre_partieplante');
	
    function findByIdBraldun($id_braldun) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('coffre_partieplante', '*')
		->from('type_partieplante', '*')
		->from('type_plante', '*')
		->where('id_fk_braldun_coffre_partieplante = '.intval($id_braldun))
		->where('coffre_partieplante.id_fk_type_coffre_partieplante = type_partieplante.id_type_partieplante')
		->where('coffre_partieplante.id_fk_type_plante_coffre_partieplante = type_plante.id_type_plante')
		->order(array('nom_type_plante', 'nom_type_partieplante'));
		$sql = $select->__toString();

		return $db->fetchAll($sql);
    }
    
	function insertOrUpdate($data) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('coffre_partieplante', 'count(*) as nombre, quantite_coffre_partieplante as quantiteBrute,  quantite_preparee_coffre_partieplante as quantitePreparee')
		->where('id_fk_type_coffre_partieplante = ?',$data["id_fk_type_coffre_partieplante"])
		->where('id_fk_braldun_coffre_partieplante = ?',$data["id_fk_braldun_coffre_partieplante"])
		->where('id_fk_type_plante_coffre_partieplante = ?',$data["id_fk_type_plante_coffre_partieplante"])
		->group(array('quantiteBrute', 'quantitePreparee'));
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		if (count($resultat) == 0) { // insert
			$this->insert($data);
		} else { // update
			$nombre = $resultat[0]["nombre"];
			$quantiteBrute = $resultat[0]["quantiteBrute"];
			$quantitePreparee = $resultat[0]["quantitePreparee"];
			
			$dataUpdate['quantite_coffre_partieplante']  = $quantiteBrute;
			$dataUpdate['quantite_preparee_coffre_partieplante']  = $quantitePreparee;
			
			if (isset($data["quantite_coffre_partieplante"])) {
				$quantiteBrute += $data["quantite_coffre_partieplante"];
			};
			
			if (isset($data["quantite_preparee_coffre_partieplante"])) {
				$quantitePreparee += $data["quantite_preparee_coffre_partieplante"];
			};
			
			$dataUpdate = array(
					'quantite_coffre_partieplante' => $quantiteBrute,
					'quantite_preparee_coffre_partieplante' => $quantitePreparee,
			);
			
			$where = ' id_fk_type_coffre_partieplante = '.$data["id_fk_type_coffre_partieplante"];
			$where .= ' AND id_fk_braldun_coffre_partieplante = '.$data["id_fk_braldun_coffre_partieplante"];
			$where .= ' AND id_fk_type_plante_coffre_partieplante = '.$data["id_fk_type_plante_coffre_partieplante"];
			$this->update($dataUpdate, $where);
		}
	}
}
