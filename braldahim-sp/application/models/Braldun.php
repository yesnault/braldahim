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
class Braldun extends Zend_Db_Table {
	protected $_name = 'braldun';
	protected $_primary = 'id_braldun';

	function findAllByDateCreationAndRegion($dateDebut, $dateFin) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('braldun', 'count(id_braldun) as nombre');
		$select->from('region', 'nom_region');
		$select->where('date_creation_braldun >= ?', $dateDebut);
		$select->where('date_creation_braldun <= ?', $dateFin);
		$select->where('est_compte_actif_braldun = ?', 'oui');
		$select->where('id_region = id_fk_region_creation_braldun');
		$select->order("nom_region ASC");
		$select->group("nom_region");
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function countByNiveau($niveau) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('braldun', 'count(id_braldun) as nombre');
		$select->where('niveau_braldun = ?', $niveau);
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		$nombre = $resultat[0]["nombre"];
		return $nombre;
	}

	public function findById($id){
		$where = $this->getAdapter()->quoteInto('id_braldun = ?',(int)$id);
		return $this->fetchRow($where);
	}

	function selectVue($x_min, $y_min, $x_max, $y_max, $z, $sansBraldunCourant = -1, $avecIntangibles = true) {
		$db = $this->getAdapter();
		$select = $db->select();

		$select->from('braldun', '*');
		$select->where('x_braldun <= ?',$x_max);
		$select->where('x_braldun >= ?',$x_min);
		$select->where('y_braldun >= ?',$y_min);
		$select->where('y_braldun <= ?',$y_max);
		$select->where('z_braldun = ?',$z);
		$select->where('est_ko_braldun = ?', "non");
		$select->where('est_compte_actif_braldun = ?', "oui");
		$select->where('est_en_hibernation_braldun = ?', "non");
		$select->where('est_pnj_braldun = ?', "non");

		if ($avecIntangibles == false) {
			$select->where("est_intangible_braldun like ?", "non");
		}
			
		if ($sansBraldunCourant != -1) {
			$select->where('id_braldun != ?',$sansBraldunCourant);
		}
		$select->joinLeft('communaute','id_fk_communaute_braldun = id_communaute');

		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}


}