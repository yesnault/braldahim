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
class BraldunsMetiers extends Zend_Db_Table {
	protected $_name = 'bralduns_metiers';
	protected $_referenceMap    = array(
		'Braldun' => array(
			'columns'           => array('id_fk_braldun_hmetier'),
			'refTableClass'     => 'Braldun',
			'refColumns'        => array('id')
	),
		'Metier' => array(
			'columns'           => array('id_fk_metier_hmetier'),
			'refTableClass'     => 'Metier',
			'refColumns'        => array('id_metier')
	)
	);

	public function countAllByMetier() {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('bralduns_metiers', 'count(id_metier) as nombre')
		->from('metier', 'nom_masculin_metier')
		->where('id_metier = id_fk_metier_hmetier')
		->group('metier.nom_masculin_metier');
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

}