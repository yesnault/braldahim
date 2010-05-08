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


class BraldunsCdm extends Zend_Db_Table {
	protected $_name = 'bralduns_cdm';
	
    function findByIdBraldunAndIdTypeMonstre($idBraldun, $idTypeMonstre) {
		//Retourne vrai si le nombre de cdm effectué suffit pour pister ce type de monstre
    	$db = $this->getAdapter();
		$select = $db->select();
		$select->from('taille_monstre', '*')
		->where('nb_cdm_taille_monstre > 0');
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);
		$tailleManquante = null;
		foreach ($resultat as $taille) {
			$select = $db->select();
			$select->from('bralduns_cdm', 'count(*) as nb_cdm')
			->where('bralduns_cdm.id_fk_braldun_hcdm = '.intval($idBraldun))
			->where('bralduns_cdm.id_fk_type_monstre_hcdm = '.intval($idTypeMonstre))
			->where('bralduns_cdm.id_fk_taille_monstre_hcdm = '.intval($taille["id_taille_monstre"]))
			->group('id_fk_taille_monstre_hcdm');
			$sql = $select->__toString();
			$resultatb = $db->fetchAll($sql);
			if (count($resultatb) == 0 || $resultatb[0]['nb_cdm'] < $taille["nb_cdm_taille_monstre"]) {
				if (count($resultatb) == 0) {
					$tailleManquante[] = Array (
						'taille' => $taille['nom_taille_m_monstre'],
						'nb_manquant' => '0/'.$taille["nb_cdm_taille_monstre"],
					);
				}
				else {
					$tailleManquante[] = Array (
						'taille' => $taille['nom_taille_m_monstre'],
						'nb_manquant' => $resultatb[0]['nb_cdm'].'/'.$taille["nb_cdm_taille_monstre"],
					);
				}
			}
		}
		return $tailleManquante;	
    }
    
    function findByIdBraldun($idBraldun) {
    	$db = $this->getAdapter();
		$select = $db->select();
		$select->from('bralduns_cdm', 'id_fk_type_monstre_hcdm')
		->from('type_monstre', 'nom_type_monstre')
		->where('bralduns_cdm.id_fk_braldun_hcdm = '.intval($idBraldun))
		->where('bralduns_cdm.id_fk_type_monstre_hcdm = type_monstre.id_type_monstre')
		->group('id_fk_type_monstre_hcdm');
		$sql = $select->__toString();
		return $db->fetchAll($sql);
    }

    function findTaille() {
    	$db = $this->getAdapter();
		$select = $db->select();
		$select->from('taille_monstre', '*')
		->where('nb_cdm_taille_monstre > 0');
		$sql = $select->__toString();
		return $db->fetchAll($sql);
    }
    
    function insertOrUpdate($data) {
    	$db = $this->getAdapter();
		$select = $db->select();
		$select->from('bralduns_cdm', 'count(*) as nb_cdm')
		->from('taille_monstre', '*')
		->where('bralduns_cdm.id_fk_braldun_hcdm = '.$data["id_fk_braldun_hcdm"])
		->where('id_fk_type_monstre_hcdm = '.$data["id_fk_type_monstre_hcdm"])
		->where('id_fk_taille_monstre_hcdm = id_taille_monstre')
		->where('id_fk_taille_monstre_hcdm = '.$data["id_fk_taille_monstre_hcdm"])
		->group('id_taille_monstre');
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);
		//Si le nombre de cdm pour cette taille est inférieur au nombre de cdm minimun on peut insérer
		if (count($resultat)==0 || $resultat[0]['nb_cdm'] < $resultat[0]['nb_cdm_taille_monstre']){
			$select = $db->select();
			$select->from('bralduns_cdm', '*')
			->where('id_fk_braldun_hcdm = ?', $data["id_fk_braldun_hcdm"])
	        ->where('id_fk_monstre_hcdm = ?', $data["id_fk_monstre_hcdm"])
	        ->where('id_fk_type_monstre_hcdm = ?', $data["id_fk_type_monstre_hcdm"])
	        ->where('id_fk_taille_monstre_hcdm = ?', $data["id_fk_taille_monstre_hcdm"]);
	        $sql = $select->__toString();
	        $resultat = $db->fetchAll($sql);
	        //Si une cdm pour ce monstre et pour cette taille n'existe pas pour ce braldun on insère.
	    	if ( count($resultat) == 0) { // insert
				$this->insert($data);
			}
    	}
    }
}