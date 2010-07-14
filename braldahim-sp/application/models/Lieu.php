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
class Lieu extends Zend_Db_Table {
	protected $_name = 'lieu';
	protected $_primary = 'id_lieu';

	public function findById($id){
		$where = $this->getAdapter()->quoteInto('id_lieu = ?',(int)$id);
		return $this->fetchRow($where);
	}

	public function findByType($type, $estSoule = null) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('lieu', '*')
		->from('type_lieu', '*')
		->where('lieu.id_fk_type_lieu = ?',$type)
		->where('lieu.id_fk_type_lieu = type_lieu.id_type_lieu')
		->joinLeft('ville','id_fk_ville_lieu = id_ville');
		if ($estSoule != null) {
			$select->where('lieu.est_soule_lieu = ?',$estSoule);
		}
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}

	public function findByTypeAndRegion($type, $region, $estSoule = null, $estCapitale = null) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('lieu', '*')
		->from('type_lieu', '*')
		->from('region', '*')
		->where('region.id_region = ?', $region)
		->where('lieu.id_fk_type_lieu = ?', $type)
		->where('lieu.id_fk_type_lieu = type_lieu.id_type_lieu')
		->where('lieu.x_lieu >= region.x_min_region')
		->where('lieu.x_lieu <= region.x_max_region')
		->where('lieu.y_lieu >= region.y_min_region')
		->where('lieu.y_lieu <= region.y_max_region')
		->joinLeft('ville','id_fk_ville_lieu = id_ville');

		if ($estSoule != null) {
			$select->where('lieu.est_soule_lieu = ?',$estSoule);
		}

		if ($estCapitale != null) {
			$select->where('ville.est_capitale_ville = ?',$estCapitale);
		}

		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}

	function selectVue($x_min, $y_min, $x_max, $y_max, $z) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('lieu', '*')
		->from('type_lieu', '*')
		->where('x_lieu <= ?',$x_max)
		->where('x_lieu >= ?',$x_min)
		->where('y_lieu >= ?',$y_min)
		->where('y_lieu <= ?',$y_max)
		->where('z_lieu = ?',$z)
		->where('lieu.id_fk_type_lieu = type_lieu.id_type_lieu');
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}

	function findNomById($id) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('lieu', '*')
		->from('type_lieu', '*')
		->where('id_lieu = ?',$id)
		->where('lieu.id_fk_type_lieu = type_lieu.id_type_lieu');
		$sql = $select->__toString();

		$lieu = $db->fetchRow($sql);
		if ($lieu == null) {
			$retour = "lieu inconnu";
		} else {
			$retour = $lieu["nom_lieu"]. " (".$lieu["id_lieu"].")";
		}
		return $retour;
	}

	function findByCase($x, $y, $z) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('lieu', '*')
		->from('type_lieu', '*')
		->where('x_lieu = ?',$x)
		->where('y_lieu = ?',$y)
		->where('z_lieu = ?',$z)
		->where('lieu.id_fk_type_lieu = type_lieu.id_type_lieu')
		->joinLeft('ville','id_fk_ville_lieu = id_ville');
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}

	function findByTypeAndCase($type,$x, $y) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('lieu', '*')
		->from('type_lieu', '*')
		->where('x_lieu = ?',$x)
		->where('y_lieu = ?',$y)
		->where('lieu.id_fk_type_lieu = ?',$type)
		->where('lieu.id_fk_type_lieu = type_lieu.id_type_lieu')
		->joinLeft('ville','id_fk_ville_lieu = id_ville');
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}

	public function findByTypeAndPosition($type, $x, $y, $estSoule = null) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('lieu', '*, SQRT(((x_lieu - '.$x.') * (x_lieu - '.$x.')) + ((y_lieu - '.$y.') * ( y_lieu - '.$y.'))) as distance')
		->from('type_lieu', '*')
		->where('lieu.id_fk_type_lieu = ?',$type)
		->where('lieu.id_fk_type_lieu = type_lieu.id_type_lieu')
		->joinLeft('ville','id_fk_ville_lieu = id_ville')
		->order(array('distance ASC'));

		if ($estSoule != null) {
			$select->where('lieu.est_soule_lieu = ?',$estSoule);
		}

		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	public function findByPositionMax($x, $y, $max) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('lieu', '*, SQRT(((x_lieu - '.$x.') * (x_lieu - '.$x.')) + ((y_lieu - '.$y.') * ( y_lieu - '.$y.'))) as distance')
		->from('type_lieu', '*')
		->where('lieu.id_fk_type_lieu = type_lieu.id_type_lieu')
		->where('SQRT(((x_lieu - '.$x.') * (x_lieu - '.$x.')) + ((y_lieu - '.$y.') * ( y_lieu - '.$y.'))) <= ?', $max)
		->joinLeft('ville','id_fk_ville_lieu = id_ville')
		->order(array('distance ASC'));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	public function findAllLieuQueteAvecRegion() {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('lieu', '*')
		->from('type_lieu', '*')
		->from('ville', '*')
		->from('region', '*')
		->where('lieu.id_fk_type_lieu = type_lieu.id_type_lieu')
		->where('lieu.id_fk_ville_lieu = id_ville')
		->where('ville.id_fk_region_ville = region.id_region')
		->where('nom_systeme_type_lieu = ?', 'quete');
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}