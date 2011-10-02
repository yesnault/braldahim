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
class BraldunsMetiers extends Zend_Db_Table
{
	protected $_name = 'bralduns_metiers';
	protected $_referenceMap = array(
		'Braldun' => array(
			'columns' => array('id_fk_braldun_hmetier'),
			'refTableClass' => 'Braldun',
			'refColumns' => array('id')
		),
		'Metier' => array(
			'columns' => array('id_fk_metier_hmetier'),
			'refTableClass' => 'Metier',
			'refColumns' => array('id_metier')
		)
	);

	public function countAllByMetier()
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('bralduns_metiers', 'id_fk_metier_hmetier')
			->from('metier', 'nom_masculin_metier')
			->from('braldun', 'count(id_braldun) as nombre')
			->where('id_metier = id_fk_metier_hmetier')
			->where('id_braldun = id_fk_braldun_hmetier')
			->group(array('metier.nom_masculin_metier', 'id_metier'));
		$select->where('est_pnj_braldun = ?', "non");

		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

}