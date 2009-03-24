<?php
/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id:$
 * $Author:$
 * $LastChangedDate:$
 * $LastChangedRevision:$
 * $LastChangedBy:$
 */


class HobbitsCdm extends Zend_Db_Table {
	protected $_name = 'hobbits_cdm';
	
    function findByIdHobbit($idHobbit) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('hobbits_cdm', 'count(*) as nbCdm')
		->from('type_monstre', '*')
		->from('taille_monstre', 'nom_taille_m_monstre')
		->where('hobbits_cdm.id_fk_hobbit_hcdm = '.intval($idHobbit))
		->where('hobbits_cdm.id_fk_type_monstre_hcdm = type_monstre.id_type_monstre')
		->where('hobbits_cdm.id_fk_taille_monstre_hcdm = taille_monstre.id_taille_monstre')
		->group(array('id_type_monstre','nom_taille_m_monstre'));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
    }

    function findByIdHobbitAndIdTypeMonstre($idHobbit,$idTypeMonstre) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('hobbits_cdm', 'count(*) as nbCdm')
		->from('taille_monstre', 'nom_taille_m_monstre')
		->where('hobbits_cdm.id_fk_hobbit_hcdm = '.intval($idHobbit))
		->where('hobbits_cdm.id_fk_type_monstre_hcdm = '.intval($idTypeMonstre))
		->where('hobbits_cdm.id_fk_taille_monstre_hcdm = taille_monstre.id_taille_monstre')
		->group('nom_taille_m_monstre');
		$sql = $select->__toString();
		return $db->fetchAll($sql);
    }
    
    function insertOrUpdate($data) {
    	$db = $this->getAdapter();
		$select = $db->select();
		$select->from('hobbits_cdm', '*')
		->where('id_fk_monstre_hcdm = ?',$data["id_fk_monstre_hcdm"])
		->where('id_fk_type_monstre_hcdm = ?',$data["id_fk_type_monstre_hcdm"])
		->where('id_fk_taille_monstre_hcdm = ?',$data["id_fk_taille_monstre_hcdm"]);
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);
		
    	if (count($resultat) == 0) { // insert
			$this->insert($data);
		}
    }
}