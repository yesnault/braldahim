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
class CharrettePartieplante extends Zend_Db_Table {
	protected $_name = 'charrette_partieplante';
	protected $_primary = array('id_fk_type_charrette_partieplante', 'id_fk_braldun_charrette_partieplante');
	
    function findByIdCharrette($idCharrette) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('charrette_partieplante', '*')
		->from('type_partieplante', '*')
		->from('type_plante', '*')
		->where('id_fk_charrette_partieplante = '.intval($idCharrette))
		->where('charrette_partieplante.id_fk_type_charrette_partieplante = type_partieplante.id_type_partieplante')
		->where('charrette_partieplante.id_fk_type_plante_charrette_partieplante = type_plante.id_type_plante')
		->order(array('nom_type_plante', 'nom_type_partieplante'));
		$sql = $select->__toString();

		return $db->fetchAll($sql);
    }
    
	function insertOrUpdate($data) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('charrette_partieplante', 'count(*) as nombre, quantite_charrette_partieplante as quantiteBrute,  quantite_preparee_charrette_partieplante as quantitePreparee')
		->where('id_fk_type_charrette_partieplante = ?',$data["id_fk_type_charrette_partieplante"])
		->where('id_fk_charrette_partieplante = ?',$data["id_fk_charrette_partieplante"])
		->where('id_fk_type_plante_charrette_partieplante = ?',$data["id_fk_type_plante_charrette_partieplante"])
		->group(array('quantiteBrute', 'quantitePreparee'));
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		if (count($resultat) == 0) { // insert
			$this->insert($data);
		} else { // update
			$nombre = $resultat[0]["nombre"];
			$quantiteBrute = $resultat[0]["quantiteBrute"];
			$quantitePreparee = $resultat[0]["quantitePreparee"];
			
			$dataUpdate['quantite_charrette_partieplante']  = $quantiteBrute;
			$dataUpdate['quantite_preparee_charrette_partieplante']  = $quantitePreparee;
			
			if (isset($data["quantite_charrette_partieplante"])) {
				$quantiteBrute += $data["quantite_charrette_partieplante"];
			}
			
			if (isset($data["quantite_preparee_charrette_partieplante"])) {
				$quantitePreparee += $data["quantite_preparee_charrette_partieplante"];
			}
			
			$dataUpdate = array(
					'quantite_charrette_partieplante' => $quantiteBrute,
					'quantite_preparee_charrette_partieplante' => $quantitePreparee,
			);
			
			$where = ' id_fk_type_charrette_partieplante = '.$data["id_fk_type_charrette_partieplante"];
			$where .= ' AND id_fk_charrette_partieplante = '.$data["id_fk_charrette_partieplante"];
			$where .= ' AND id_fk_type_plante_charrette_partieplante = '.$data["id_fk_type_plante_charrette_partieplante"];
			
			$this->update($dataUpdate, $where);
		}
	}
}
