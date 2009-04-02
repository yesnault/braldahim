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
class HobbitsMetiers extends Zend_Db_Table
{
	protected $_name = 'hobbits_metiers';
	protected $_referenceMap    = array(
		'Hobbit' => array(
			'columns'           => array('id_fk_hobbit_hmetier'),
			'refTableClass'     => 'Hobbit',
			'refColumns'        => array('id')
		),
		'Metier' => array(
			'columns'           => array('id_fk_metier_hmetier'),
			'refTableClass'     => 'Metier',
			'refColumns'        => array('id_metier')
		)
	);

	public function findMetiersByHobbitId($idHobbit) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('hobbits_metiers', '*')
		->from('metier', '*')
		->where('hobbits_metiers.id_fk_metier_hmetier = metier.id_metier')
		->where('hobbits_metiers.id_fk_hobbit_hmetier = '.intval($idHobbit))
		->order('metier.nom_masculin_metier');
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}
	
	public function findMetierCourantByHobbitId($idHobbit) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('hobbits_metiers', '*')
		->from('metier', '*')
		->where('hobbits_metiers.id_fk_metier_hmetier = metier.id_metier')
		->where('hobbits_metiers.id_fk_hobbit_hmetier = '.intval($idHobbit))
		->where('est_actif_hmetier = ?', 'oui')
		->order('metier.nom_masculin_metier');
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}
	
	public function findMetiersEchoppeByHobbitId($idHobbit) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('hobbits_metiers', '*')
		->from('metier', '*')
		->where('hobbits_metiers.id_fk_metier_hmetier = metier.id_metier')
		->where('hobbits_metiers.id_fk_hobbit_hmetier = '.intval($idHobbit))
		->where("metier.construction_echoppe_metier = 'oui'")
		->order('metier.nom_masculin_metier');
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}

	public function peutPossederEchoppeIdHobbit($idHobbit) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('hobbits_metiers', 'count(id_fk_metier_hmetier) as nombre')
		->from('metier', 'id_metier')
		->where("metier.construction_echoppe_metier = 'oui'")
		->where("hobbits_metiers.id_fk_hobbit_hmetier = ".intval($idHobbit))
		->where("hobbits_metiers.id_fk_metier_hmetier = metier.id_metier")
		->group("id_metier");
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);
		
		if (!isset($resultat[0]) || $resultat[0]["nombre"] <1) {
			return false;
		} else {
			return true;
		}
	}
}