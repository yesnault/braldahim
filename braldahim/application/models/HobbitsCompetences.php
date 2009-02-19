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
class HobbitsCompetences extends Zend_Db_Table {
    protected $_name = 'hobbits_competences';
	protected $_referenceMap    = array(
        'Hobbit' => array(
            'columns'           => array('id_fk_hobbit_hcomp'),
            'refTableClass'     => 'Hobbit',
            'refColumns'        => array('id_hobbit')
        ),
        'Competence' => array(
            'columns'           => array('id_fk_competence_hcomp'),
            'refTableClass'     => 'Competence',
            'refColumns'        => array('id_competence')
        )
	);
	
    function findByIdHobbit($idHobbit) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('hobbits_competences', '*')
		->from('competence', '*')
		->where('hobbits_competences.id_fk_hobbit_hcomp = '.intval($idHobbit))
		->where('hobbits_competences.id_fk_competence_hcomp = competence.id_competence')
		->order('ordre_competence ASC');
		$sql = $select->__toString();
		return $db->fetchAll($sql);
    }
    
    function annuleEffetsTabacByIdHobbit($idHobbit) {
		$where  = 'hobbits_competences.id_fk_hobbit_hcomp = '.intval($idHobbit);
		$data = array(
			'nb_tour_restant_bonus_tabac_hcomp' => 0,
			'nb_tour_restant_malus_tabac_hcomp' => 0
			);
		$this->update($data, $where);
    }
}