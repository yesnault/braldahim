<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: $
 * $Author: $
 * $LastChangedDate: $
 * $LastChangedRevision: $
 * $LastChangedBy: $
 */
class Champ extends Zend_Db_Table {
	protected $_name = 'champ';
	protected $_primary = "id_champ";

	function countAll() {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('champ', 'count(id_champ) as nombre');
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);
		$nombre = $resultat[0]["nombre"];
		return $nombre;
	}

	function countVue($x_min, $y_min, $x_max, $y_max, $z) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('champ', 'count(id_champ) as nombre')
		->where('x_champ <= ?',$x_max)
		->where('x_champ >= ?',$x_min)
		->where('y_champ >= ?',$y_min)
		->where('y_champ <= ?',$y_max)
		->where('z_champ = ?',$z);
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);
		$nombre = $resultat[0]["nombre"];
		return $nombre;
	}

	function selectVue($x_min, $y_min, $x_max, $y_max, $z) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('champ', '*')
		->from('braldun', array('nom_braldun', 'prenom_braldun', 'sexe_braldun', 'id_braldun'))
		->where('x_champ <= ?',$x_max)
		->where('x_champ >= ?',$x_min)
		->where('y_champ >= ?',$y_min)
		->where('y_champ <= ?',$y_max)
		->where('z_champ = ?',$z)
		->where('braldun.id_braldun = champ.id_fk_braldun_champ');
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findByCase($x, $y, $z, $idBraldun = null) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('champ', '*')
		->from('braldun', '*')
		->from('region', '*')
		->where('x_champ = ?',$x)
		->where('y_champ = ?',$y)
		->where('z_champ = ?',$z)
		->where('id_fk_braldun_champ = id_braldun')
		->where('region.x_min_region <= champ.x_champ')
		->where('region.x_max_region >= champ.x_champ')
		->where('region.y_min_region <= champ.y_champ')
		->where('region.y_max_region >= champ.y_champ');

		if ($idBraldun != null) {
			$select->where('id_braldun = ?', $idBraldun);
		}

		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}

	function findByIdBraldun($id_braldun) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('champ', '*')
		->from('region', '*')
		->where('id_fk_braldun_champ = ?', $id_braldun)
		->where('region.x_min_region <= champ.x_champ')
		->where('region.x_max_region >= champ.x_champ')
		->where('region.y_min_region <= champ.y_champ')
		->where('region.y_max_region >= champ.y_champ');

		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findById($id) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('champ', '*')
		->where('id_champ = ?', $id);
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function selectSemerARecolter() {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('champ', '*')
		->from('braldun', array('nom_braldun', 'prenom_braldun', 'sexe_braldun', 'id_braldun'))
		->where('braldun.id_braldun = champ.id_fk_braldun_champ')
		->where('phase_champ = ?', 'seme')
		->where('date_fin_seme_champ <= ?', date('Y-m-d H:i:s'));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function selectFinARecolter() {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('champ', '*')
		->from('braldun', array('nom_braldun', 'prenom_braldun', 'sexe_braldun', 'id_braldun'))
		->where('braldun.id_braldun = champ.id_fk_braldun_champ')
		->where('phase_champ = ?', 'a_recolter')
		->where('date_fin_recolte_champ <= ?', date('Y-m-d H:i:s'));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

}
