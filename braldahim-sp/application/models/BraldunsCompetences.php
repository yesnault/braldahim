<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: BraldunsCompetences.php 1451 2009-04-19 16:49:56Z yvonnickesnault $
 * $Author: yvonnickesnault $
 * $LastChangedDate: 2009-04-19 18:49:56 +0200 (dim., 19 avr. 2009) $
 * $LastChangedRevision: 1451 $
 * $LastChangedBy: yvonnickesnault $
 */
class BraldunsCompetences extends Zend_Db_Table {
    protected $_name = 'bralduns_competences';
	protected $_referenceMap    = array(
        'Braldun' => array(
            'columns'           => array('id_fk_braldun_hcomp'),
            'refTableClass'     => 'Braldun',
            'refColumns'        => array('id_braldun')
        ),
        'Competence' => array(
            'columns'           => array('id_fk_competence_hcomp'),
            'refTableClass'     => 'Competence',
            'refColumns'        => array('id_competence')
        )
	);
	
    function findByIdBraldun($idBraldun) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('bralduns_competences', '*')
		->from('competence', '*')
		->where('bralduns_competences.id_fk_braldun_hcomp = '.intval($idBraldun))
		->where('bralduns_competences.id_fk_competence_hcomp = competence.id_competence')
		->order('ordre_competence ASC');
		$sql = $select->__toString();
		return $db->fetchAll($sql);
    }
    
    function findByIdBraldunAndMetierCourant($idBraldun) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('bralduns_competences', '*')
		->from('competence', '*')
		->from('bralduns_metiers', '*')
		->where('bralduns_competences.id_fk_braldun_hcomp = ?', intval($idBraldun))
		->where('competence.id_fk_metier_competence = bralduns_metiers.id_fk_metier_hmetier')
		->where('bralduns_competences.id_fk_competence_hcomp = competence.id_competence')
		->where('bralduns_metiers.id_fk_braldun_hmetier = bralduns_competences.id_fk_braldun_hcomp')
		->where('bralduns_metiers.est_actif_hmetier = ?', 'oui')
		->order('ordre_competence ASC');
		$sql = $select->__toString();
		return $db->fetchAll($sql);
    }
    
    function findByIdBraldunAndNbPaAndNomSystemeMetier($idBraldun, $nbPa, $nomSystemeMetier) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('bralduns_competences', '*')
		->from('competence', '*')
		->from('metier', '*')
		->where('bralduns_competences.id_fk_braldun_hcomp = ?', intval($idBraldun))
		->where('bralduns_competences.id_fk_competence_hcomp = competence.id_competence')
		->where('competence.pa_utilisation_competence = ?', intval($nbPa))
		->where('competence.id_fk_metier_competence = id_metier')
		->where('metier.nom_systeme_metier = ?', $nomSystemeMetier)
		->order('ordre_competence ASC');
		$sql = $select->__toString();
		return $db->fetchAll($sql);
    }
    
    function findByIdBraldunAndNomSysteme($idBraldun, $nomSysteme) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('bralduns_competences', '*')
		->from('competence', '*')
		->where('bralduns_competences.id_fk_braldun_hcomp = ?', intval($idBraldun))
		->where('bralduns_competences.id_fk_competence_hcomp = competence.id_competence')
		->where('competence.nom_systeme_competence = ?', $nomSysteme);
		$sql = $select->__toString();
		return $db->fetchAll($sql);
    }
    
    function annuleEffetsTabacByIdBraldun($idBraldun) {
		$where  = 'bralduns_competences.id_fk_braldun_hcomp = '.intval($idBraldun);
		$data = array(
			'nb_tour_restant_bonus_tabac_hcomp' => 0,
			'nb_tour_restant_malus_tabac_hcomp' => 0
			);
		$this->update($data, $where);
    }
}