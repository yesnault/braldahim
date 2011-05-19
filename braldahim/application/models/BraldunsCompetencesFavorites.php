<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class BraldunsCompetencesFavorites extends Zend_Db_Table {
	protected $_name = 'bralduns_competences_favorites';
	protected $_referenceMap    = array(
        'Braldun' => array(
            'columns'           => array('id_fk_braldun_hcompf'),
            'refTableClass'     => 'Braldun',
            'refColumns'        => array('id_braldun')
	),
        'Competence' => array(
            'columns'           => array('id_fk_competence_hcompf'),
            'refTableClass'     => 'Competence',
            'refColumns'        => array('id_competence')
	)
	);

	function findByIdBraldun($idBraldun) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('bralduns_competences_favorites', '*')
		->from('competence', '*')
		->where('bralduns_competences_favorites.id_fk_braldun_hcompf = '.intval($idBraldun))
		->where('bralduns_competences_favorites.id_fk_competence_hcompf = competence.id_competence')
		->order('ordre_competence ASC');
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findByIdBraldunAndIdCompetence($idBraldun, $idCompetence) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('bralduns_competences_favorites', '*')
		->from('competence', '*')
		->where('bralduns_competences_favorites.id_fk_braldun_hcompf = ?', intval($idBraldun))
		->where('bralduns_competences_favorites.id_fk_competence_hcompf = competence.id_competence')
		->where('competence.id_competence = ?', intval($idCompetence));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}