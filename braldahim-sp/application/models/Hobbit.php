<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: Hobbit.php 1786 2009-06-28 20:39:53Z yvonnickesnault $
 * $Author: yvonnickesnault $
 * $LastChangedDate: 2009-06-28 22:39:53 +0200 (dim., 28 juin 2009) $
 * $LastChangedRevision: 1786 $
 * $LastChangedBy: yvonnickesnault $
 */
class Hobbit extends Zend_Db_Table {
	protected $_name = 'hobbit';
	protected $_primary = 'id_hobbit';

	function findAllByDateCreationAndRegion($dateDebut, $dateFin) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('hobbit', 'count(id_hobbit) as nombre');
		$select->from('region', 'nom_region');
		$select->where('date_creation_hobbit >= ?', $dateDebut);
		$select->where('date_creation_hobbit <= ?', $dateFin);
		$select->where('est_compte_actif_hobbit = ?', 'oui');
		$select->where('id_region = id_fk_region_creation_hobbit');
		$select->order("nom_region ASC");
		$select->group("nom_region");
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function countByNiveau($niveau) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('hobbit', 'count(id_hobbit) as nombre');
		$select->where('niveau_hobbit = ?', $niveau);
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		$nombre = $resultat[0]["nombre"];
		return $nombre;
	}

	public function findById($id){
		$where = $this->getAdapter()->quoteInto('id_hobbit = ?',(int)$id);
		return $this->fetchRow($where);
	}

	function selectVue($x_min, $y_min, $x_max, $y_max, $z, $sansHobbitCourant = -1, $avecIntangibles = true) {
		$db = $this->getAdapter();
		$select = $db->select();

		$select->from('hobbit', '*');
		$select->where('x_hobbit <= ?',$x_max);
		$select->where('x_hobbit >= ?',$x_min);
		$select->where('y_hobbit >= ?',$y_min);
		$select->where('y_hobbit <= ?',$y_max);
		$select->where('z_hobbit = ?',$z);
		$select->where('est_ko_hobbit = ?', "non");
		$select->where('est_compte_actif_hobbit = ?', "oui");
		$select->where('est_en_hibernation_hobbit = ?', "non");
		$select->where('est_pnj_hobbit = ?', "non");

		if ($avecIntangibles == false) {
			$select->where("est_intangible_hobbit like ?", "non");
		}
			
		if ($sansHobbitCourant != -1) {
			$select->where('id_hobbit != ?',$sansHobbitCourant);
		}
		$select->joinLeft('communaute','id_fk_communaute_hobbit = id_communaute');

		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}


}